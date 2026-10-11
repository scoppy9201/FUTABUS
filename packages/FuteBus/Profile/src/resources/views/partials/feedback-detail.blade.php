@php
    $inDialog = request()->boolean('modal');
    $reference = __('Profile::feedback.reference', ['id' => str_pad((string) $report->id, 6, '0', STR_PAD_LEFT)]);
    $stages = ['received' => $report->created_at, 'reviewing' => $report->reviewing_at, 'resolved' => $report->resolved_at];
    $activeStage = array_search($report->status, array_keys($stages), true);
    $emptyResponse = $report->status === 'resolved' ? 'response_empty_resolved' : 'response_pending';
    $statusColor = match ($report->status) {
        'resolved' => 'bg-green-50 text-futa-green',
        'reviewing' => 'bg-blue-50 text-blue-700',
        default => 'bg-futa-orange-soft text-futa-orange',
    };
@endphp
<article data-feedback-report class="min-w-0">
    <header class="flex items-start justify-between gap-4 border-b border-gray-100 p-5 sm:px-6">
        <div class="min-w-0">
            <p class="text-xs font-bold text-futa-orange">{{ $reference }}</p>
            <h2 class="mt-1 break-words text-lg font-bold text-gray-950">{{ $report->subject }}</h2>
            <p class="mt-2 text-xs text-slate-500">{{ __('Profile::feedback.sent_at', ['date' => \Illuminate\Support\Carbon::parse($report->created_at)->format('d/m/Y H:i')]) }}</p>
        </div>
        @if($inDialog)
            <button type="button" data-feedback-close aria-label="{{ __('Profile::feedback.close') }}"
                class="grid size-9 shrink-0 place-items-center rounded-full text-slate-500 hover:bg-gray-100">
                <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
            </button>
        @endif
    </header>
    <div class="space-y-6 p-5 sm:p-6">
        <section aria-labelledby="feedback-progress-title">
            <div class="flex items-center justify-between gap-3">
                <h3 id="feedback-progress-title" class="text-sm font-bold text-gray-950">{{ __('Profile::feedback.progress') }}</h3>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusColor }}">{{ __('Profile::feedback.status.'.$report->status) }}</span>
            </div>
            <ol class="mt-4 grid grid-cols-3">
                @foreach($stages as $stage => $timestamp)
                    @php $reached = $loop->index <= $activeStage; @endphp
                    <li class="relative flex min-w-0 flex-col items-center px-1 text-center" @if($stage === $report->status) aria-current="step" @endif>
                        @if(!$loop->last)
                            <span aria-hidden="true" @class([
                                'absolute left-1/2 right-[-50%] top-4 h-0.5',
                                'bg-futa-green' => $loop->index < $activeStage, 'bg-gray-200' => $loop->index >= $activeStage,
                            ])></span>
                        @endif
                        <span @class([
                            'relative z-10 grid size-8 place-items-center rounded-full ring-4 ring-white',
                            'bg-futa-green text-white' => $reached, 'bg-gray-100 text-slate-400' => !$reached,
                        ])>
                            @if($reached)<x-heroicon-o-check class="size-4" aria-hidden="true" />@else<span class="text-xs font-semibold">{{ $loop->iteration }}</span>@endif
                        </span>
                        <p @class(['mt-2 text-xs font-semibold', 'text-futa-green' => $stage === $report->status, 'text-slate-500' => $stage !== $report->status])>
                            {{ __('Profile::feedback.status.'.$stage) }}
                        </p>
                        @if($timestamp)
                            <p class="mt-1 text-[11px] text-slate-400">{{ \Illuminate\Support\Carbon::parse($timestamp)->format('d/m/Y H:i') }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        </section>
        <section class="rounded-xl border border-futa-orange/25 bg-futa-orange-soft/40 p-4" aria-labelledby="feedback-response-title">
            <h3 id="feedback-response-title" class="flex items-center gap-2 text-sm font-bold text-gray-950">
                <x-heroicon-o-chat-bubble-left-right class="size-5 text-futa-orange" aria-hidden="true" />{{ __('Profile::feedback.response_title') }}
            </h3>
            <p class="mt-2 whitespace-pre-wrap break-words text-sm leading-6 text-slate-600">{{ filled($report->response) ? $report->response : __('Profile::feedback.'.$emptyResponse) }}</p>
        </section>
        <section aria-labelledby="feedback-report-content-title">
            <h3 id="feedback-report-content-title" class="text-sm font-bold text-gray-950">{{ __('Profile::feedback.content_section') }}</h3>
            <dl class="mt-3 grid gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-xs text-slate-500">{{ __('Profile::feedback.fields.category') }}</dt><dd class="mt-1 font-semibold">{{ __('Profile::feedback.categories.'.$report->category) }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Profile::feedback.fields.urgency') }}</dt><dd class="mt-1 font-semibold">{{ __('Profile::feedback.urgencies.'.$report->urgency) }}</dd></div>
                @if($report->booking_code)
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-slate-500">{{ __('Profile::feedback.fields.booking_id') }}</dt>
                        <dd class="mt-1 font-semibold">{{ $report->booking_code }}</dd>
                        @if($booking)
                            <dd class="mt-1 text-slate-500">{{ $booking->origin_city }} → {{ $booking->destination_city }} · {{ \Illuminate\Support\Carbon::parse($booking->departure_time)->format('d/m/Y H:i') }}</dd>
                        @endif
                    </div>
                @endif
                <div class="sm:col-span-2">
                    <dt class="text-xs text-slate-500">{{ __('Profile::feedback.fields.message') }}</dt>
                    <dd class="mt-2 whitespace-pre-wrap break-words leading-6 text-slate-700">{{ $report->message }}</dd>
                </div>
            </dl>
            @if($attachments->isNotEmpty())
                <h4 class="mt-4 text-xs font-semibold text-slate-500">{{ __('Profile::feedback.fields.attachments') }}</h4>
                <div class="mt-2 grid grid-cols-3 gap-3">
                    @foreach($attachments as $attachment)
                        <a href="{{ route('profile.feedback.attachment', ['report' => $report->id, 'attachment' => $attachment->id]) }}"
                            target="_blank" rel="noopener" class="min-w-0 rounded-xl border border-gray-200 p-1.5 hover:border-futa-orange">
                            <img src="{{ route('profile.feedback.attachment', ['report' => $report->id, 'attachment' => $attachment->id]) }}"
                                alt="{{ $attachment->original_name }}" loading="lazy" class="h-28 w-full rounded-lg object-cover">
                            <p class="mt-1 truncate text-xs text-slate-500">{{ $attachment->original_name }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
        <section class="border-t border-gray-100 pt-4" aria-labelledby="feedback-report-contact-title">
            <h3 id="feedback-report-contact-title" class="text-sm font-bold text-gray-950">{{ __('Profile::feedback.contact_section') }}</h3>
            <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                @foreach(['contact_name', 'contact_phone', 'contact_email'] as $key)
                    @if(filled($report->{$key}))
                        <div><dt class="text-xs text-slate-500">{{ __('Profile::feedback.fields.'.$key) }}</dt><dd class="mt-1 break-words font-semibold">{{ $report->{$key} }}</dd></div>
                    @endif
                @endforeach
            </dl>
            <p class="mt-3 text-xs text-slate-500">{{ __('Profile::feedback.'.($report->allow_contact ? 'contact_allowed' : 'contact_not_allowed')) }}</p>
        </section>
    </div>
    <footer class="flex flex-wrap justify-end gap-3 border-t border-gray-100 p-4 sm:px-6">
        <a href="tel:19006067" class="inline-flex min-h-11 items-center gap-2 rounded-full border border-gray-200 px-4 text-sm font-semibold text-slate-600">
            <x-heroicon-o-phone class="size-4" aria-hidden="true" />1900 6067
        </a>
        @if($inDialog)
            <button type="button" data-feedback-close class="min-h-11 rounded-full bg-futa-orange px-6 text-sm font-bold text-white hover:bg-futa-orange-dark">{{ __('Profile::feedback.close') }}</button>
        @else
            <a href="{{ route('profile.feedback.index') }}" class="inline-flex min-h-11 items-center rounded-full bg-futa-orange px-5 text-sm font-bold text-white">{{ __('Profile::feedback.back') }}</a>
        @endif
    </footer>
</article>
