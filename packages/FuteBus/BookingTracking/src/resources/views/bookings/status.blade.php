@php
    $labels = __('BookingTracking::bookings.'.($group ?? 'statuses'));
    $label = $labels[$status] ?? __('BookingTracking::bookings.unknown');
@endphp
<span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
    'bg-green-50 text-green-700' => in_array($status, ['confirmed', 'completed', 'active', 'paid', 'arrived', 'refunded']),
    'bg-red-50 text-red-700' => in_array($status, ['cancelled', 'failed']),
    'bg-amber-50 text-amber-700' => in_array($status, ['pending', 'needs_review']),
    'bg-slate-100 text-slate-600' => ! in_array($status, ['confirmed', 'completed', 'active', 'paid', 'arrived', 'refunded', 'cancelled', 'failed', 'pending', 'needs_review']),
])>{{ $label }}</span>
