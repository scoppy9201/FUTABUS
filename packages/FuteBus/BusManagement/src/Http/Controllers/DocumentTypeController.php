<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Controllers;

use FuteBus\BusCompanyManagement\Models\BusCompany as BusCompanyRecord;
use FuteBus\BusManagement\Http\Requests\DocumentTypeRequest;
use FuteBus\BusManagement\Models\DocumentType as DocumentTypeRecord;
use FuteBus\BusManagement\Models\DocumentTypeField as DocumentTypeFieldRecord;
use FuteBus\BusManagement\Models\VehicleDocument as VehicleDocumentRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentTypeController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $search = (string) $request->input('search', '');
        $query = DocumentTypeRecord::query()->toBase()
            ->selectRaw('document_types.*, (select count(*) from vehicle_documents
                         where vehicle_documents.document_type_id = document_types.id) as record_count')
            ->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', "%$search%");
        }

        $types = $query->paginate(15)->withQueryString();
        $fields = DocumentTypeFieldRecord::query()->toBase()
            ->whereIn('document_type_id', $types->pluck('id'))
            ->where('is_active', 1)->orderBy('sort_order')->get()
            ->groupBy('document_type_id');

        return view('BusManagement::document-types.index', [
            'types'   => $types,
            'fields'  => $fields,
            'search'  => $search,
            'company' => BusCompanyRecord::query()->toBase()->where('code', 'FUTA')->first(),
        ]);
    }

    public function store(DocumentTypeRequest $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $id = DocumentTypeRecord::query()->toBase()->insertGetId([
                'name'            => trim($data['name']),
                'description'     => $data['description'] ?? null,
                'is_required'     => (bool) $data['is_required'],
                'has_expiry'      => (bool) ($data['has_expiry'] ?? true),
                'blocks_dispatch' => (bool) ($data['blocks_dispatch'] ?? (bool) $data['is_required']),
                'warning_days'    => (int) ($data['warning_days'] ?? 15),
                'status'          => 'active',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            foreach (array_values($data['fields']) as $i => $f) {
                $this->insertField($id, $f, $i);
            }
        });

        return redirect()->route('bus-management.document-types.index')
            ->with('success', __('BusManagement::app.dt_flash_created'));
    }

    public function update(DocumentTypeRequest $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $type = DocumentTypeRecord::query()->toBase()->find($id);
        abort_unless($type, 404);

        if ($type->status !== 'active') {
            return back()->withErrors(['type' => __('BusManagement::app.dt_err_inactive')]);
        }

        $data = $request->validated();

        DB::transaction(function () use ($data, $id) {
            DocumentTypeRecord::query()->toBase()->where('id', $id)->update([
                'name'            => trim($data['name']),
                'description'     => $data['description'] ?? null,
                'is_required'     => (bool) $data['is_required'],
                'has_expiry'      => (bool) ($data['has_expiry'] ?? true),
                'blocks_dispatch' => (bool) ($data['blocks_dispatch'] ?? (bool) $data['is_required']),
                'warning_days'    => (int) ($data['warning_days'] ?? 15),
                'updated_at'      => now(),
            ]);

            $existing = DocumentTypeFieldRecord::query()->toBase()->where('document_type_id', $id)->pluck('id')->all();
            $keep = [];

            foreach (array_values($data['fields']) as $i => $f) {
                $fid = isset($f['id']) && in_array((int) $f['id'], $existing, true) ? (int) $f['id'] : null;
                if ($fid) {
                    DocumentTypeFieldRecord::query()->toBase()->where('id', $fid)->update([
                        'label'       => trim($f['label']),
                        'is_required' => (bool) $f['required'],
                        'sort_order'  => $i,
                        'is_active'   => 1,
                        'updated_at'  => now(),
                    ]);
                    $keep[] = $fid;
                } else {
                    $keep[] = $this->insertField($id, $f, $i);
                }
            }

            // Ẩn trường bị bỏ, không xóa (bảo toàn dữ liệu hồ sơ cũ)
            DocumentTypeFieldRecord::query()->toBase()->where('document_type_id', $id)
                ->whereNotIn('id', $keep)->update(['is_active' => 0, 'updated_at' => now()]);
        });

        return redirect()->route('bus-management.document-types.index')
            ->with('success', __('BusManagement::app.dt_flash_updated'));
    }

    /** Ngừng sử dụng (hoặc xóa nếu chưa có hồ sơ). */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $type = DocumentTypeRecord::query()->toBase()->find($id);
        abort_unless($type, 404);

        $recordCount = (int) VehicleDocumentRecord::query()->toBase()->where('document_type_id', $id)->count();

        // Nếu có hồ sơ: chỉ ngừng sử dụng
        if ($recordCount > 0) {
            return back()->withErrors(['type' => __('BusManagement::app.dt_err_has_records')]);
        }

        // Chưa có hồ sơ: xóa hoàn toàn
        DocumentTypeFieldRecord::query()->toBase()->where('document_type_id', $id)->delete();
        DocumentTypeRecord::query()->toBase()->where('id', $id)->delete();

        return redirect()->route('bus-management.document-types.index')
            ->with('success', __('BusManagement::app.dt_flash_deleted'));
    }

    /** Ngừng sử dụng / Kích hoạt lại. */
    public function toggle(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $type = DocumentTypeRecord::query()->toBase()->find($id);
        abort_unless($type, 404);

        $newStatus = $type->status === 'active' ? 'inactive' : 'active';
        DocumentTypeRecord::query()->toBase()->where('id', $id)->update([
            'status'     => $newStatus,
            'updated_at' => now(),
        ]);

        $msgKey = $newStatus === 'active' ? 'dt_flash_activated' : 'dt_flash_deactivated';

        return redirect()->route('bus-management.document-types.index')
            ->with('success', __('BusManagement::app.'.$msgKey));
    }

    private function insertField(int $typeId, array $f, int $order): int
    {
        return DocumentTypeFieldRecord::query()->toBase()->insertGetId([
            'document_type_id' => $typeId,
            'field_key'        => Str::lower(Str::random(8)),
            'label'            => trim($f['label']),
            'field_type'       => $f['type'] ?? 'text',
            'is_required'      => (bool) $f['required'],
            'is_active'        => 1,
            'sort_order'       => $order,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }
}
