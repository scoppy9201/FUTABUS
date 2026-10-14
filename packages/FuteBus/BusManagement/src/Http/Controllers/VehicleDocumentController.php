<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Controllers;

use FuteBus\BusCompanyManagement\Models\BusCompany as BusCompanyRecord;
use FuteBus\BusManagement\Http\Requests\VehicleDocumentRequest;
use FuteBus\BusManagement\Models\Bus as BusRecord;
use FuteBus\BusManagement\Models\DocumentType as DocumentTypeRecord;
use FuteBus\BusManagement\Models\DocumentTypeField as DocumentTypeFieldRecord;
use FuteBus\BusManagement\Models\VehicleDocument as VehicleDocumentRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        $query = VehicleDocumentRecord::query()->toBase()
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

        $types = DocumentTypeRecord::query()->toBase()->where('status', 'active')->orderBy('name')->get();
        $dbFields = DocumentTypeFieldRecord::query()->toBase()
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
            'buses'       => BusRecord::query()->toBase()->orderBy('license_plate')->get(['id', 'license_plate', 'status']),
            'allTypes'    => $types,
            'typePayload' => $typePayload,
            'company'     => BusCompanyRecord::query()->toBase()->where('code', 'FUTA')->first(),
        ]);
    }

    public function file(Request $request, int $id)
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $doc = VehicleDocumentRecord::query()->toBase()->find($id);
        abort_unless($doc && $doc->file_path && Storage::disk('local')->exists($doc->file_path), 404);

        $fileName = Str::replace(['"', "\r", "\n"], '', basename($doc->file_name ?: $doc->file_path));

        return response()->file(Storage::disk('local')->path($doc->file_path), [
            'Content-Disposition'    => 'inline; filename="'.$fileName.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function store(VehicleDocumentRequest $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $docType = DocumentTypeRecord::query()->toBase()
            ->where('id', $request->input('document_type_id'))
            ->where('status', 'active')
            ->first();

        $hasExpiry = (bool) ($docType?->has_expiry ?? true);

        $data = $request->validated();

        // Tạo hồ sơ mới sẽ đánh dấu hồ sơ cũ là superseded (thông qua renewed_from_id)
        $existingId = $this->getCurrentDocId((int) $data['bus_id'], (int) $data['document_type_id']);

        $values = $request->fieldValues();

        $filePath = null;
        $fileName = null;
        if ($request->hasFile('attachment')) {
            ['path' => $filePath, 'name' => $fileName] = $this->storeFile($request);
        }

        VehicleDocumentRecord::query()->toBase()->insert([
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

    public function update(VehicleDocumentRequest $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $doc = VehicleDocumentRecord::query()->toBase()->find($id);
        abort_unless($doc, 404);

        $docType = DocumentTypeRecord::query()->toBase()->find($doc->document_type_id);
        $hasExpiry = (bool) ($docType?->has_expiry ?? true);

        $data = $request->validated();

        $values = $request->fieldValues();

        $updateData = [
            'issued_date'  => $data['issued_date'],
            'expiry_date'  => $hasExpiry ? ($data['expiry_date'] ?? null) : null,
            'field_values' => json_encode($values, JSON_UNESCAPED_UNICODE),
            'updated_at'   => now(),
        ];

        if ($request->hasFile('attachment')) {
            ['path' => $updateData['file_path'], 'name' => $updateData['file_name']] = $this->storeFile($request);
        }

        VehicleDocumentRecord::query()->toBase()->where('id', $id)->update($updateData);
        if ($request->hasFile('attachment') && $doc->file_path) {
            Storage::disk('local')->delete($doc->file_path);
        }

        return redirect()->route('bus-management.vehicle-documents.index')
            ->with('success', __('BusManagement::app.vd_flash_updated'));
    }

    /** Gia hạn: tạo hồ sơ mới thay thế hồ sơ cũ (cơ chế version). */
    public function renew(VehicleDocumentRequest $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $old = VehicleDocumentRecord::query()->toBase()->find($id);
        abort_unless($old, 404);

        if ($old->status !== 'active' || VehicleDocumentRecord::query()->toBase()->where('renewed_from_id', $id)->exists()) {
            return back()->withErrors(['doc' => __('BusManagement::app.vd_err_history')]);
        }

        $docType = DocumentTypeRecord::query()->toBase()
            ->where('id', $old->document_type_id)->where('status', 'active')->first();
        if (! $docType) {
            return back()->withErrors(['doc' => __('BusManagement::app.vd_err_type')]);
        }

        $data = $request->validated();

        $values = $request->fieldValues();

        $filePath = null;
        $fileName = null;
        if ($request->hasFile('attachment')) {
            ['path' => $filePath, 'name' => $fileName] = $this->storeFile($request);
        }

        VehicleDocumentRecord::query()->toBase()->insert([
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
        $doc = VehicleDocumentRecord::query()->toBase()->find($id);
        abort_unless($doc, 404);

        if (VehicleDocumentRecord::query()->toBase()->where('renewed_from_id', $id)->exists()) {
            return back()->withErrors(['doc' => __('BusManagement::app.vd_err_has_child')]);
        }

        VehicleDocumentRecord::query()->toBase()->where('id', $id)->delete();
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
        $doc = VehicleDocumentRecord::query()->toBase()->find($id);
        abort_unless($doc, 404);

        if ($doc->status !== 'active') {
            return back()->withErrors(['doc' => __('BusManagement::app.vd_err_inactive')]);
        }

        VehicleDocumentRecord::query()->toBase()->where('id', $id)->update([
            'status'     => 'inactive',
            'updated_at' => now(),
        ]);

        return redirect()->route('bus-management.vehicle-documents.index')
            ->with('warning', __('BusManagement::app.vd_flash_deactivated'));
    }

    /** ID của hồ sơ hiện hành (active + chưa bị superseded). */
    private function getCurrentDocId(int $busId, int $typeId): ?int
    {
        return VehicleDocumentRecord::query()->toBase()
            ->where('bus_id', $busId)->where('document_type_id', $typeId)->where('status', 'active')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('vehicle_documents as n')
                    ->whereColumn('n.renewed_from_id', 'vehicle_documents.id');
            })->value('id');
    }

    private function storeFile(Request $request): array
    {
        $file = $request->file('attachment');
        $path = $file->store('vehicle-documents', 'local');

        return ['path' => $path, 'name' => $file->getClientOriginalName()];
    }
}
