<span @class([
    'inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold',
    'bg-emerald-50 text-emerald-700' => in_array($value, ['active', 'confirmed', 'completed', 'arrived'], true),
    'bg-amber-50 text-amber-700' => in_array($value, ['pending', 'scheduled', 'maintenance'], true),
    'bg-rose-50 text-rose-700' => $value === 'cancelled',
    'bg-slate-100 text-slate-600' => in_array($value, ['inactive', 'departed'], true),
])>{{ __('Dashboard::app.status.'.$value) }}</span>
