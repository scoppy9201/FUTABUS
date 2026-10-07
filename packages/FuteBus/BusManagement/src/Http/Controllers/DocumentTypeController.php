<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentTypeController extends Controller
{
    private const FIELD_TYPES = ['text', 'number', 'date'];

    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $search = (string) $request->input('search', '');
        $query  = DB::table('document_types')
            ->selectRaw('document_types.*, (select count(*) from vehicle_documents
                         where vehicle_documents.document_type_id = document_types.id) as record_count')
            ->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', "%$search%");
        }

        $types  = $query->paginate(10)->withQueryString();
        $fields = DB::table('document_type_fields')
            ->whereIn('document_type_id', $types->pluck('id'))
            ->where('is_active', 1)->orderBy('sort_order')->get()
            ->groupBy('document_type_id');

        return view('BusManagement::document-types.index', [
            'types'   => $types,
            'fields'  => $fields,
            'search'  => $search,
            'company' => DB::table('bus_companies')->where('code', 'FUTA')->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate($this->rules(), $this->messages());

        DB::transaction(function () use ($data) {
            $id = DB::table('document_types')->insertGetId([
                'name'        => trim($data['name']),
                'description' => $data['description'] ?? null,
                'is_required' => (bool) $data['is_required'],
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            foreach (array_values($data['fields']) as $i => $f) {
                $this->insertField($id, $f, $i);
            }
        });

        return redirect()->route('bus-management.document-types.index')
            ->with('success', __('BusManagement::app.dt_flash_created'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $type = DB::table('document_types')->find($id);
        abort_unless($type, 404);

        if ($type->status !== 'active') { // 7d
            return back()->withErrors(['type' => __('BusManagement::app.dt_err_inactive')]);
        }

        $data = $request->validate($this->rules($id), $this->messages());

        DB::transaction(function () use ($data, $id) {
            DB::table('document_types')->where('id', $id)->update([
                'name'        => trim($data['name']),
                'description' => $data['description'] ?? null,
                'is_required' => (bool) $data['is_required'],
                'updated_at'  => now(),
            ]);

            $existing = DB::table('document_type_fields')->where('document_type_id', $id)->pluck('id')->all();
            $keep = [];

            foreach (array_values($data['fields']) as $i => $f) {
                $fid = isset($f['id']) && in_array((int) $f['id'], $existing, true) ? (int) $f['id'] : null;
                if ($fid) {
                    DB::table('document_type_fields')->where('id', $fid)->update([
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

            // Trường bị bỏ: chỉ ẩn, không xóa => dữ liệu hồ sơ cũ được bảo toàn
            DB::table('document_type_fields')->where('document_type_id', $id)
                ->whereNotIn('id', $keep)->update(['is_active' => 0, 'updated_at' => now()]);
        });

        return redirect()->route('bus-management.document-types.index')
            ->with('success', __('BusManagement::app.dt_flash_updated'));
    }

    /** Ngừng sử dụng. */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $type = DB::table('document_types')->find($id);
        abort_unless($type, 404);

        if ($type->status !== 'active') { // 7d
            return back()->withErrors(['type' => __('BusManagement::app.dt_err_inactive')]);
        }
        if ($type->is_required) { // 7e
            return back()->withErrors(['type' => __('BusManagement::app.dt_err_required')]);
        }

        $inUse = DB::table('vehicle_documents')
            ->join('buses', 'buses.id', '=', 'vehicle_documents.bus_id')
            ->where('vehicle_documents.document_type_id', $id)
            ->where('vehicle_documents.status', 'active')
            ->where('buses.status', 'active')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('vehicle_documents as n')
                  ->whereColumn('n.renewed_from_id', 'vehicle_documents.id');
            })->exists();

        if ($inUse) { // 7c
            return back()->withErrors(['type' => __('BusManagement::app.dt_err_in_use')]);
        }

        DB::table('document_types')->where('id', $id)->update(['status' => 'inactive', 'updated_at' => now()]);

        return redirect()->route('bus-management.document-types.index')
            ->with('warning', __('BusManagement::app.dt_flash_deactivated'));
    }

    private function insertField(int $typeId, array $f, int $order): int
    {
        return DB::table('document_type_fields')->insertGetId([
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

    private function rules(?int $id = null): array
    {
        return [
            'name'              => ['required', 'string', 'max:100', Rule::unique('document_types', 'name')->ignore($id)], // 7a
            'description'       => ['nullable', 'string', 'max:500'],
            'is_required'       => ['required', 'boolean'],
            'fields'            => ['required', 'array', 'min:1'],                                                         // 7b
            'fields.*.id'       => ['nullable', 'integer'],
            'fields.*.label'    => ['required', 'string', 'max:100'],
            'fields.*.type'     => ['nullable', Rule::in(self::FIELD_TYPES)],
            'fields.*.required' => ['required', 'boolean'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.unique'     => __('BusManagement::app.dt_err_name'),
            'fields.required' => __('BusManagement::app.dt_err_fields'),
            'fields.min'      => __('BusManagement::app.dt_err_fields'),
        ];
    }
}