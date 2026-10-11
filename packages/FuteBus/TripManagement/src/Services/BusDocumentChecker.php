<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BusDocumentChecker
{
    /** Trả về danh sách thông báo lỗi (rỗng nếu xe đủ giấy tờ bắt buộc, còn hạn tại ngày khởi hành). */
    public static function messages(object $bus, Carbon $departure): array
    {
        $types = DB::table('document_types')
            ->where('is_required', 1)->where('status', 'active')->get(['id', 'name']);

        if ($types->isEmpty()) {
            return [];
        }

        $current = DB::table('vehicle_documents')
            ->where('bus_id', $bus->id)
            ->whereIn('document_type_id', $types->pluck('id'))
            ->where('status', 'active')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('vehicle_documents as n')
                    ->whereColumn('n.renewed_from_id', 'vehicle_documents.id');
            })
            ->get()->keyBy('document_type_id');

        $day = $departure->toDateString();
        $out = [];

        foreach ($types as $t) {
            $doc = $current->get($t->id);
            if (! $doc) {
                $out[] = __('TripManagement::app.tr_err_doc_missing', ['type' => $t->name]);
            } elseif ($doc->expiry_date < $day) {
                $out[] = __('TripManagement::app.tr_err_doc_expired', [
                    'type' => $t->name,
                    'date' => Carbon::parse($doc->expiry_date)->format('d/m/Y'),
                ]);
            }
        }

        return $out
            ? array_merge([__('TripManagement::app.tr_err_docs_head', ['plate' => $bus->license_plate])], $out)
            : [];
    }
}
