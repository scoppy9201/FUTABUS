<span @class(['inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold',
    'bg-amber-100 text-amber-800' => $status === 'refund_pending',
    'bg-blue-100 text-blue-700' => $status === 'refund_processing',
    'bg-green-100 text-green-700' => $status === 'refunded',
    'bg-red-100 text-red-700' => $status === 'refund_failed',
    'bg-slate-100 text-slate-600' => !in_array($status, ['refund_pending', 'refund_processing', 'refunded', 'refund_failed'], true)])>
    <span aria-hidden="true" class="size-1.5 rounded-full bg-current"></span>{{ __('Payment::refunds.statuses.'.$status) }}
</span>
