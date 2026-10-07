<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehicleDocumentController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $search = (string) $request->input('search', '');
        $busId  = (string) $request->input('bus_id', '');
        $state  = (string) $request->input('state', '');
        $today  = today()->toDateString();
        $soon   = today()->addDays(30)->toDateString();

        $query = DB::table('vehicle_documents')
            ->join('buses', 'buses.id', '=', 'vehicle_documents.bus_id')
            ->join('document_types', 'document_types.id', '=', 'vehicle_documents.document_type_id')
            ->select('vehicle_documents.*', 'buses.license_plate', 'document_types.name as type_name',
                     'document_types.is_required')
            ->selectRaw('exists(select 1 from vehicle_documents n
                         where n.renewed_from_id = vehicle_documents.id) as superseded')
            ->orderBy('buses.license_plate')
            ->orderBy('document_types.name')
            ->orderByDesc('vehicle_documents.id');

        if ($search !== '') {
            $query->where(fn ($q) => $q->where('buses.license_plate', 'like', "%$search%")
                ->orWhere('document_types.name', 'like', "%$search%"));
        }
        if ($busId !== '') {
            $query->where('vehicle_documents.bus_id', $busId);
        }
        match ($state) {
            'expired'  => $query->where('vehicle_documents.expiry_date', '<', $today),
            'expiring' => $query->whereBetween('vehicle_documents.expiry_date', [$today, $soon]),
            'valid'    => $query->where('vehicle_documents.expiry_date', '>', $soon),
            default    => null,
        };

        $types  = DB::table('document_types')->where('status', 'active')->orderBy('name')->get();
        $fields = DB::table('document_type_fields')
            ->whereIn('document_type_id', $types->pluck('id'))
            ->where('is_active', 1)->orderBy('sort_order')->get()->groupBy('document_type_id');

        $typePayload = $types->map(fn ($t) => [
            'id'     => $t->id,
            'name'   => $t->name,
            'fields' => ($fields[$t->id] ?? collect())->map(fn ($f) => [
                'key' => $f->field_key, 'label' => $f->label,
                'type' => $f->field_type, 'required' => (bool) $f->is_required,
            ])->values(),
        ])->values();

        return view('BusManagement::vehicle-documents.index', [
            'docs'        => $query->paginate(10)->withQueryString(),
            'search'      => $search,
            'busId'       => $busId,
            'state'       => $state,
            'buses'       => DB::table('buses')->orderBy('license_plate')->get(['id', 'license_plate', 'status']),
            'typePayload' => $typePayload,
            'company'     => DB::table('bus_companies')->where('code', 'FUTA')->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate([
            'bus_id'           => ['required', Rule::exists('buses', 'id')->where('status', 'active')],
            'document_type_id' => ['required', Rule::exists('document_types', 'id')->where('status', 'active')],
            'issued_date'      => ['required', 'date'],
            'expiry_date'      => ['required', 'date', 'after:issued_date'],            // 9b
        ], $this->messages());

        if ($this->currentExists((int) $data['bus_id'], (int) $data['document_type_id'])) { // 9c
            return back()->withInput()->withErrors(['doc' => __('BusManagement::app.vd_err_duplicate')]);
        }

        $values = $this->validatedValues($request, (int) $data['document_type_id']);

        DB::table('vehicle_documents')->insert([
            'bus_id'           => $data['bus_id'],
            'document_type_id' => $data['document_type_id'],
            'issued_date'      => $data['issued_date'],
            'expiry_date'      => $data['expiry_date'],
            'field_values'     => json_encode($values, JSON_UNESCAPED_UNICODE),
            'status'           => 'active',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('bus-management.vehicle-documents.index')
            ->with('success', __('BusManagement::app.vd_flash_created'));
    }

    /** Gia hạn: tạo hồ sơ mới, giữ nguyên hồ sơ cũ. */
    public function renew(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $old = DB::table('vehicle_documents')->find($id);
        abort_unless($old, 404);

        if ($old->status !== 'active' || DB::table('vehicle_documents')->where('renewed_from_id', $id)->exists()) {
            return back()->withErrors(['doc' => __('BusManagement::app.vd_err_history')]);
        }
        if (Carbon::parse($old->expiry_date)->gte(today())) {
            return back()->withErrors(['doc' => __('BusManagement::app.vd_err_not_expired')]);
        }
        $typeOk = DB::table('document_types')->where('id', $old->document_type_id)->where('status', 'active')->exists();
        if (! $typeOk) {
            return back()->withErrors(['doc' => __('BusManagement::app.vd_err_type')]);
        }

        $data = $request->validate([
            'issued_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after:issued_date'],               // 9b
        ], $this->messages());

        $values = $this->validatedValues($request, (int) $old->document_type_id);

        DB::table('vehicle_documents')->insert([
            'bus_id'           => $old->bus_id,
            'document_type_id' => $old->document_type_id,
            'renewed_from_id'  => $old->id,
            'issued_date'      => $data['issued_date'],
            'expiry_date'      => $data['expiry_date'],
            'field_values'     => json_encode($values, JSON_UNESCAPED_UNICODE),
            'status'           => 'active',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('bus-management.vehicle-documents.index')
            ->with('success', __('BusManagement::app.vd_flash_renewed'));
    }

    /** Ngừng sử dụng hồ sơ (không xóa, không đổi dữ liệu đã lưu). */
    public function deactivate(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $doc = DB::table('vehicle_documents')->find($id);
        abort_unless($doc, 404);

        if ($doc->status !== 'active') {
            return back()->withErrors(['doc' => __('BusManagement::app.vd_err_inactive')]);
        }
        $type = DB::table('document_types')->find($doc->document_type_id);
        if ($type?->is_required) { // 9e
            return back()->withErrors(['doc' => __('BusManagement::app.vd_err_required')]);
        }
        $busy = Schema::hasTable('trips') && DB::table('trips')->where('bus_id', $doc->bus_id)
            ->whereIn('status', ['scheduled', 'departed'])->exists();
        if ($busy) { // 9d
            return back()->withErrors(['doc' => __('BusManagement::app.vd_err_trip')]);
        }

        DB::table('vehicle_documents')->where('id', $id)->update(['status' => 'inactive', 'updated_at' => now()]);

        return redirect()->route('bus-management.vehicle-documents.index')
            ->with('warning', __('BusManagement::app.vd_flash_deactivated'));
    }

    /** Hồ sơ hiện hành: đang active và chưa bị gia hạn. */
    private function currentExists(int $busId, int $typeId): bool
    {
        return DB::table('vehicle_documents')
            ->where('bus_id', $busId)->where('document_type_id', $typeId)->where('status', 'active')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('vehicle_documents as n')
                  ->whereColumn('n.renewed_from_id', 'vehicle_documents.id');
            })->exists();
    }

    /** 9a: kiểm tra các trường theo cấu hình của loại giấy tờ, trả về snapshot. */
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

    private function messages(): array
    {
        return ['expiry_date.after' => __('BusManagement::app.vd_err_expiry')];
    }
}