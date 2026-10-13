<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehicleDocumentController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $search = (string) $request->input('search', '');
        $busId = (string) $request->input('bus_id', '');
        $typeId = (string) $request->input('doc_type_id', '');
        $state = (string) $request->input('state', '');
        $today = today()->toDateString();
        $soon = today()->addDays(30)->toDateString();

        $query = DB::table('vehicle_documents')
            ->join('buses', 'buses.id', '=', 'vehicle_documents.bus_id')
            ->join('document_types', 'document_types.id', '=', 'vehicle_documents.document_type_id')
            ->select(
                'vehicle_documents.*',
                'buses.license_plate',
                'document_types.name as type_name',
                'document_types.is_required',
                'document_types.has_expiry',
                'document_types.warning_days',
            )
            ->selectRaw('exists(select 1 from vehicle_documents n
                         where n.renewed_from_id = vehicle_documents.id) as superseded')
            ->orderBy('vehicle_documents.expiry_date')  // mặc định sắp xếp theo hạn tăng dần
            ->orderBy('buses.license_plate')
            ->orderByDesc('vehicle_documents.id');

        if ($search !== '') {
            $query->where(fn ($q) => $q
                ->where('buses.license_plate', 'like', "%$search%")
                ->orWhere('document_types.name', 'like', "%$search%"));
        }
        if ($busId !== '') {
            $query->where('vehicle_documents.bus_id', $busId);
        }
        if ($typeId !== '') {
            $query->where('vehicle_documents.document_type_id', $typeId);
        }
        match ($state) {
            'expired'  => $query->where('vehicle_documents.expiry_date', '<', $today),
            'expiring' => $query->whereBetween('vehicle_documents.expiry_date', [$today, $soon]),
            'valid'    => $query->where(function ($q) use ($soon) {
                $q->whereNull('vehicle_documents.expiry_date')->orWhere('vehicle_documents.expiry_date', '>', $soon);
            }),
            'no_expiry' => $query->whereNull('vehicle_documents.expiry_date'),
            default     => null,
        };

        $types = DB::table('document_types')->where('status', 'active')->orderBy('name')->get();
        $dbFields = DB::table('document_type_fields')
            ->whereIn('document_type_id', $types->pluck('id'))
            ->where('is_active', 1)->orderBy('sort_order')->get()->groupBy('document_type_id');

        $typePayload = $types->map(fn ($t) => [
            'id'             => $t->id,
            'name'           => $t->name,
            'has_expiry'     => (bool) ($t->has_expiry ?? true),
            'blocks_dispatch'=> (bool) ($t->blocks_dispatch ?? true),
            'warning_days'   => (int) ($t->warning_days ?? 15),
            'fields'         => ($dbFields[$t->id] ?? collect())->map(fn ($f) => [
                'key'      => $f->field_key, 'label' => Lang::has('BusManagement::app.'.$f->label) ? __('BusManagement::app.'.$f->label) : $f->label,
                'type'     => $f->field_type, 'required' => (bool) $f->is_required,
            ])->values(),
        ])->values();

        return view('BusManagement::vehicle-documents.index', [
            'docs'        => $query->paginate(15)->withQueryString(),
            'search'      => $search,
            'busId'       => $busId,
            'typeId'      => $typeId,
            'state'       => $state,
            'buses'       => DB::table('buses')->orderBy('license_plate')->get(['id', 'license_plate', 'status']),
            'allTypes'    => $types,
            'typePayload' => $typePayload,
            'company'     => DB::table('bus_companies')->where('code', 'FUTA')->first(),
        ]);
    }

    public function file(Request $request, int $id)
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $doc = DB::table('vehicle_documents')->find($id);
        abort_unless($doc && $doc->file_path && Storage::disk('local')->exists($doc->file_path), 404);

        $fileName = Str::replace(['"', "\r", "\n"], '', basename($doc->file_name ?: $doc->file_path));

        return response()->file(Storage::disk('local')->path($doc->file_path), [
            'Content-Disposition'    => 'inline; filename="'.$fileName.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $docType = DB::table('document_types')
            ->where('id', $request->input('document_type_id'))
            ->where('status', 'active')
            ->first();

        $hasExpiry = (bool) ($docType?->has_expiry ?? true);

        $rules = [
            'bus_id'           => ['required', Rule::exists('buses', 'id')->where('status', 'active')],
            'document_type_id' => ['required', Rule::exists('document_types', 'id')->where('status', 'active')],
            'issued_date'      => ['required', 'date', 'before:today'],
            'attachment'       => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ];
        if ($hasExpiry) {
            $rules['expiry_date'] = ['required', 'date', 'after:today', 'after:issued_date'];
        }
        $data = $request->validate($rules, $this->messages());

        // Tạo hồ sơ mới sẽ đánh dấu hồ sơ cũ là superseded (thông qua renewed_from_id)
        $existingId = $this->getCurrentDocId((int) $data['bus_id'], (int) $data['document_type_id']);

        $values = $this->validatedValues($request, (int) $data['document_type_id']);

        $filePath = null;
        $fileName = null;
        if ($request->hasFile('attachment')) {
            ['path' => $filePath, 'name' => $fileName] = $this->storeFile($request);
        }

        DB::table('vehicle_documents')->insert([
            'bus_id'           => $data['bus_id'],
            'document_type_id' => $data['document_type_id'],
            'renewed_from_id'  => $existingId, // Hồ sơ cũ tự thành lịch sử
            'issued_date'      => $data['issued_date'],
            'expiry_date'      => $hasExpiry ? ($data['expiry_date'] ?? null) : null,
            'field_values'     => json_encode($values, JSON_UNESCAPED_UNICODE),
            'file_path'        => $filePath,
            'file_name'        => $fileName,
            'status'           => 'active',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('bus-management.vehicle-documents.index')
            ->with('success', __('BusManagement::app.vd_flash_created'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $doc = DB::table('vehicle_documents')->find($id);
        abort_unless($doc, 404);

        $docType = DB::table('document_types')->find($doc->document_type_id);
        $hasExpiry = (bool) ($docType?->has_expiry ?? true);

        $rules = [
            'issued_date' => ['required', 'date', 'before:today'],
            'attachment'  => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ];
        if ($hasExpiry) {
            $rules['expiry_date'] = ['required', 'date', 'after:today', 'after:issued_date'];
        }
        $data = $request->validate($rules, $this->messages());

        $values = $this->validatedValues($request, (int) $doc->document_type_id);

        $updateData = [
            'issued_date'  => $data['issued_date'],
            'expiry_date'  => $hasExpiry ? ($data['expiry_date'] ?? null) : null,
            'field_values' => json_encode($values, JSON_UNESCAPED_UNICODE),
            'updated_at'   => now(),
        ];

        if ($request->hasFile('attachment')) {
            ['path' => $updateData['file_path'], 'name' => $updateData['file_name']] = $this->storeFile($request);
        }

        DB::table('vehicle_documents')->where('id', $id)->update($updateData);
        if ($request->hasFile('attachment') && $doc->file_path) {
            Storage::disk('local')->delete($doc->file_path);
        }

        return redirect()->route('bus-management.vehicle-documents.index')
            ->with('success', __('BusManagement::app.vd_flash_updated'));
    }

    /** Gia hạn: tạo hồ sơ mới thay thế hồ sơ cũ (cơ chế version). */
    public function renew(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $old = DB::table('vehicle_documents')->find($id);
        abort_unless($old, 404);

        if ($old->status !== 'active' || DB::table('vehicle_documents')->where('renewed_from_id', $id)->exists()) {
            return back()->withErrors(['doc' => __('BusManagement::app.vd_err_history')]);
        }

        $docType = DB::table('document_types')
            ->where('id', $old->document_type_id)->where('status', 'active')->first();
        if (! $docType) {
            return back()->withErrors(['doc' => __('BusManagement::app.vd_err_type')]);
        }

        $data = $request->validate([
            'issued_date' => ['required', 'date', 'before:today'],
            'expiry_date' => ['required', 'date', 'after:today', 'after:issued_date'],
            'attachment'  => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ], $this->messages());

        $values = $this->validatedValues($request, (int) $old->document_type_id);

        $filePath = null;
        $fileName = null;
        if ($request->hasFile('attachment')) {
            ['path' => $filePath, 'name' => $fileName] = $this->storeFile($request);
        }

        DB::table('vehicle_documents')->insert([
            'bus_id'           => $old->bus_id,
            'document_type_id' => $old->document_type_id,
            'renewed_from_id'  => $old->id,
            'issued_date'      => $data['issued_date'],
            'expiry_date'      => $data['expiry_date'],
            'field_values'     => json_encode($values, JSON_UNESCAPED_UNICODE),
            'file_path'        => $filePath,
            'file_name'        => $fileName,
            'status'           => 'active',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('bus-management.vehicle-documents.index')
            ->with('success', __('BusManagement::app.vd_flash_renewed'));
    }

    /** Xóa hoàn toàn (chỉ hồ sơ nhập nhầm - chưa có record nào gia hạn từ nó). */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $doc = DB::table('vehicle_documents')->find($id);
        abort_unless($doc, 404);

        if (DB::table('vehicle_documents')->where('renewed_from_id', $id)->exists()) {
            return back()->withErrors(['doc' => __('BusManagement::app.vd_err_has_child')]);
        }

        DB::table('vehicle_documents')->where('id', $id)->delete();
        if ($doc->file_path) {
            Storage::disk('local')->delete($doc->file_path);
        }

        return redirect()->route('bus-management.vehicle-documents.index')
            ->with('success', __('BusManagement::app.vd_flash_deleted'));
    }

    /** Ngừng sử dụng hồ sơ (không xóa). */
    public function deactivate(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $doc = DB::table('vehicle_documents')->find($id);
        abort_unless($doc, 404);

        if ($doc->status !== 'active') {
            return back()->withErrors(['doc' => __('BusManagement::app.vd_err_inactive')]);
        }

        DB::table('vehicle_documents')->where('id', $id)->update([
            'status'     => 'inactive',
            'updated_at' => now(),
        ]);

        return redirect()->route('bus-management.vehicle-documents.index')
            ->with('warning', __('BusManagement::app.vd_flash_deactivated'));
    }

    /** ID của hồ sơ hiện hành (active + chưa bị superseded). */
    private function getCurrentDocId(int $busId, int $typeId): ?int
    {
        return DB::table('vehicle_documents')
            ->where('bus_id', $busId)->where('document_type_id', $typeId)->where('status', 'active')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('vehicle_documents as n')
                    ->whereColumn('n.renewed_from_id', 'vehicle_documents.id');
            })->value('id');
    }

    private function validatedValues(Request $request, int $typeId): array
    {
        $fields = DB::table('document_type_fields')
            ->where('document_type_id', $typeId)->where('is_active', 1)->orderBy('sort_order')->get();

        $rules = $attrs = [];
        foreach ($fields as $f) {
            $r = [$f->is_required ? 'required' : 'nullable', 'string', 'max:255'];
            if ($f->field_type === 'number') {
                $r[] = 'numeric';
            }
            if ($f->field_type === 'date') {
                $r[] = 'date';
            }
            $rules["values.{$f->field_key}"] = $r;
            $attrs["values.{$f->field_key}"] = $f->label;
        }

        $input = $request->validate($rules, [], $attrs);

        return $fields->map(fn ($f) => [
            'key'   => $f->field_key,
            'label' => $f->label,
            'type'  => $f->field_type,
            'value' => data_get($input, "values.{$f->field_key}"),
        ])->values()->all();
    }

    private function storeFile(Request $request): array
    {
        $file = $request->file('attachment');
        $path = $file->store('vehicle-documents', 'local');

        return ['path' => $path, 'name' => $file->getClientOriginalName()];
    }

    private function messages(): array
    {
        return [
            'expiry_date.after'           => __('BusManagement::app.vd_err_expiry'),
            'issued_date.before_or_equal' => __('BusManagement::app.vd_err_issued_future'),
            'issued_date.before'          => __('BusManagement::app.vd_err_issued_future'),
            'attachment.mimes'            => __('BusManagement::app.vd_err_attachment'),
            'attachment.max'              => __('BusManagement::app.vd_err_attachment'),
        ];
    }
}
