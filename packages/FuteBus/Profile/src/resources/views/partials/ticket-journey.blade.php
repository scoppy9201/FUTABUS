@php
    $departure = \Illuminate\Support\Carbon::parse($booking->departure_time);
    $arrival = \Illuminate\Support\Carbon::parse($booking->arrival_time);
    $bookedAt = \Illuminate\Support\Carbon::parse($booking->created_at);
    $paidAt = $payment?->paid_at ? \Illuminate\Support\Carbon::parse($payment->paid_at) : null;
    $stageIndex = match ($stage) {
        'paid' => 1,
        'departed' => 2,
        'arrived' => 3,
        'rated' => 4,
        default => 0,
    };
    $steps = ['booked', 'paid', 'departed', 'arrived', 'rated'];
    $isDemo = str_starts_with($booking->booking_code, 'DEMOHIST');
    $inDialog = $inDialog ?? false;
    $reviewTags = [];
    $reviewComment = '';
    if ($review) {
        $availableTags = array_unique(array_merge(
            __('Profile::tickets.journey.review_tags_positive'),
            __('Profile::tickets.journey.review_tags_negative'),
            trans('Profile::tickets.journey.review_tags_positive', [], 'vi'),
            trans('Profile::tickets.journey.review_tags_negative', [], 'vi'),
            trans('Profile::tickets.journey.review_tags_positive', [], 'en'),
            trans('Profile::tickets.journey.review_tags_negative', [], 'en')
        ));
        foreach (explode(' · ', (string) $review->comment) as $part) {
            if (in_array($part, $availableTags, true)) {
                $reviewTags[] = $part;
            } elseif ($part !== '') {
                $reviewComment = trim($reviewComment.' '.$part);
            }
        }
    }
    $events = [];
    if (in_array($stage, ['cancelled', 'refunded', 'payment_failed'], true)) {
        $events[] = [
            'title' => __('Profile::tickets.journey.event.'.$stage),
            'detail' => $stage === 'cancelled' ? __('Profile::tickets.journey.cancelled_detail') : null,
            'date' => $stage === 'cancelled'
                ? \Illuminate\Support\Carbon::parse($booking->updated_at)->format('d/m/Y H:i')
                : \Illuminate\Support\Carbon::parse($payment->created_at)->format('d/m/Y H:i'),
            'state' => 'error',
        ];
        $events[] = [
            'title' => __('Profile::tickets.journey.event.booked'),
            'detail' => __('Profile::tickets.journey.booked_detail', ['code' => $booking->booking_code]),
            'date' => $bookedAt->format('d/m/Y H:i'),
            'state' => 'past',
        ];
    } else {
        foreach (['rated', 'arrived', 'departed', 'paid', 'booked'] as $eventStep) {
            $eventIndex = array_search($eventStep, $steps, true);
            $eventState = $stageIndex < $eventIndex ? 'future' : ($stageIndex === $eventIndex ? 'current' : 'past');
            $eventTitle = match ($eventStep) {
                'arrived' => __('Profile::tickets.journey.event.'.($eventState === 'future' ? 'future_arrival' : ($arrivalConfirmed ? 'arrived' : 'arrival_time_passed'))),
                'departed' => __('Profile::tickets.journey.event.'.($eventState === 'future' ? 'future_departure' : ($departureConfirmed ? 'departed' : 'departure_time_passed'))),
                default => __('Profile::tickets.journey.event.'.$eventStep),
            };
            $eventDetail = match ($eventStep) {
                'rated' => $eventState === 'future' ? __('Profile::tickets.journey.review_future') : __('Profile::tickets.journey.review_star', ['count' => $review?->rating]),
                'arrived' => $booking->destination_station ?: $booking->destination_city,
                'departed' => $booking->origin_station ?: $booking->origin_city,
                'paid' => $eventState === 'future' ? __('Profile::tickets.journey.payment_future') : __('Profile::tickets.journey.paid_detail', ['amount' => number_format((float) $booking->total_amount, 0, ',', '.')]),
                'booked' => __('Profile::tickets.journey.booked_detail', ['code' => $booking->booking_code]),
            };
            $eventDate = match ($eventStep) {
                'rated' => $review ? \Illuminate\Support\Carbon::parse($review->updated_at)->format('d/m/Y H:i') : null,
                'arrived' => $arrival->format('d/m/Y H:i'),
                'departed' => $departure->format('d/m/Y H:i'),
                'paid' => $paidAt?->format('d/m/Y H:i'),
                'booked' => $bookedAt->format('d/m/Y H:i'),
            };
            $events[] = ['title' => $eventTitle, 'detail' => $eventDetail, 'date' => $eventDate, 'state' => $eventState];
        }
    }
    $bannerDetail = match ($stage) {
        'paid' => __('Profile::tickets.journey.banner_detail.paid', ['time' => $departure->format('d/m/Y H:i')]),
        'departed' => __('Profile::tickets.journey.banner_detail.departed', ['time' => $arrival->format('d/m/Y H:i')]),
        'arrived' => $arrivalConfirmed
            ? __('Profile::tickets.journey.banner_detail.arrived', ['time' => $arrival->format('d/m/Y H:i')])
            : __('Profile::tickets.journey.arrival_unconfirmed'),
        'rated' => __('Profile::tickets.journey.banner_detail.rated', ['count' => $review?->rating]),
        'cancelled' => __('Profile::tickets.journey.banner_detail.cancelled'),
        'refunded' => __('Profile::tickets.journey.banner_detail.refunded'),
        'payment_failed' => __('Profile::tickets.journey.banner_detail.payment_failed'),
        default => __('Profile::tickets.journey.banner_detail.booked'),
    };
    $tripDuration = max(1, $arrival->timestamp - $departure->timestamp);
    $journeyProgress = max(0, min(100, (int) round((now()->timestamp - $departure->timestamp) / $tripDuration * 100)));
@endphp

<article class="overflow-hidden rounded-2xl bg-white text-gray-950" aria-labelledby="journey-title">
    <header class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-3.5">
        <div class="min-w-0">
            <p class="text-xs font-semibold text-slate-500">{{ __('Profile::tickets.columns.code') }}</p>
            <h2 id="journey-title" class="mt-0.5 break-all text-sm font-extrabold text-futa-orange">{{ $booking->booking_code }}</h2>
            @if($isDemo)
                <span class="mt-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ __('Profile::tickets.demo_label') }}</span>
            @endif
        </div>
        @if($inDialog)
        <button type="button" data-history-close aria-label="{{ __('Profile::tickets.journey.close') }}"
            class="grid size-9 shrink-0 place-items-center rounded-full text-slate-500 transition hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-futa-orange">
            <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
        </button>
        @endif
    </header>

    <div class="space-y-5 px-5 py-4">
        <div @class([
            'rounded-xl px-4 py-3',
            'bg-red-50 text-red-700' => in_array($stage, ['cancelled', 'refunded', 'payment_failed'], true),
            'bg-futa-orange-soft text-futa-orange-dark' => $stage === 'departed',
            'bg-blue-50 text-blue-700' => $stage === 'paid',
            'bg-green-50 text-futa-green' => in_array($stage, ['arrived', 'rated'], true),
            'bg-slate-50 text-slate-700' => $stage === 'booked',
        ])>
            <p class="flex items-center gap-2 text-sm font-extrabold">
                @if(in_array($stage, ['cancelled', 'refunded', 'payment_failed'], true))
                    <x-heroicon-o-x-circle class="size-5 shrink-0" aria-hidden="true" />
                @elseif($stage === 'departed')
                    <x-heroicon-o-truck class="size-5 shrink-0" aria-hidden="true" />
                @elseif($stage === 'paid')
                    <x-heroicon-o-clock class="size-5 shrink-0" aria-hidden="true" />
                @else
                    <x-heroicon-o-check-circle class="size-5 shrink-0" aria-hidden="true" />
                @endif
                {{ __('Profile::tickets.journey.banner.'.$stage) }}
            </p>
            <p class="mt-1 pl-7 text-xs leading-5">{{ $bannerDetail }}</p>
            @if($stage === 'departed' && ! $departureConfirmed)
                <p class="mt-1 pl-7 text-xs">{{ __('Profile::tickets.journey.scheduled_only') }}</p>
            @endif
            @if($stage === 'departed')
                <div class="ml-7 mt-2 h-1.5 overflow-hidden rounded-full bg-black/10" role="progressbar"
                    aria-label="{{ __('Profile::tickets.journey.progress') }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $journeyProgress }}">
                    <span class="block h-full rounded-full bg-futa-orange" style="width: {{ $journeyProgress }}%"></span>
                </div>
            @endif
        </div>

        @if(! in_array($stage, ['cancelled', 'refunded', 'payment_failed'], true))
            <div class="relative">
                <span class="absolute left-[10%] right-[10%] top-4 h-0.5 bg-gray-200" aria-hidden="true"></span>
                <span class="absolute left-[10%] top-4 h-0.5 bg-futa-green" style="width: {{ $stageIndex * 20 }}%" aria-hidden="true"></span>
                <ol class="relative grid grid-cols-5" aria-label="{{ __('Profile::tickets.journey.progress') }}">
                    @foreach($steps as $index => $step)
                        <li class="flex min-w-0 flex-col items-center text-center" @if($stageIndex === $index) aria-current="step" @endif>
                            <span @class([
                                'relative z-10 grid size-8 place-items-center rounded-full border-2 text-xs font-bold',
                                'border-futa-green bg-futa-green text-white ring-4 ring-green-50' => $stageIndex === $index,
                                'border-futa-green bg-futa-green text-white' => $stageIndex > $index,
                                'border-gray-200 bg-white text-gray-400' => $stageIndex < $index,
                            ])>
                                @if($stageIndex > $index)
                                    <x-heroicon-s-check class="size-4" aria-hidden="true" />
                                @else
                                    @switch($step)
                                        @case('booked')<x-heroicon-o-ticket class="size-4" aria-hidden="true" />@break
                                        @case('paid')<x-heroicon-o-credit-card class="size-4" aria-hidden="true" />@break
                                        @case('departed')<x-heroicon-o-truck class="size-4" aria-hidden="true" />@break
                                        @case('arrived')<x-heroicon-o-flag class="size-4" aria-hidden="true" />@break
                                        @default<x-heroicon-o-star class="size-4" aria-hidden="true" />
                                    @endswitch
                                @endif
                            </span>
                            <span @class([
                                'mt-2 px-0.5 text-[10px] font-bold leading-4 sm:text-xs',
                                'text-futa-green' => $stageIndex === $index,
                                'text-gray-700' => $stageIndex > $index,
                                'text-gray-400' => $stageIndex < $index,
                            ])>{{ __('Profile::tickets.journey.step.'.$step) }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif

        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-8">
        <div class="min-w-0 space-y-5">
        @if($stage === 'rated' && $review)
            <section class="rounded-xl border border-futa-orange bg-futa-orange-soft/30 p-4" aria-labelledby="journey-review-summary-title">
                <h3 id="journey-review-summary-title" class="text-sm font-extrabold">{{ __('Profile::tickets.journey.review_your_review') }}</h3>
                <div class="mt-2 flex items-center gap-2" aria-label="{{ __('Profile::tickets.journey.review_star', ['count' => $review->rating]) }}">
                    @for($star = 1; $star <= 5; $star++)
                        <x-heroicon-s-star @class(['size-5', 'text-amber-500' => $star <= $review->rating, 'text-slate-300' => $star > $review->rating]) aria-hidden="true" />
                    @endfor
                    <span class="ml-1 text-sm font-bold text-futa-orange">{{ __('Profile::tickets.journey.review_rating_labels.'.$review->rating) }}</span>
                </div>
                @if($reviewTags)
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach($reviewTags as $tag)
                            <span class="rounded-full border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold text-gray-800">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif
                @if($reviewComment !== '')
                    <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $reviewComment }}</p>
                @endif
                <p class="mt-3 text-xs text-slate-500">{{ __('Profile::tickets.journey.review_date', ['date' => \Illuminate\Support\Carbon::parse($review->updated_at)->format('d/m/Y H:i')]) }}</p>
            </section>
        @elseif($canReview)
            <form data-history-review data-review-too-long="{{ __('Profile::tickets.journey.review_comment_too_long') }}"
                @if($inDialog) data-no-loader @endif action="{{ route('profile.tickets.review', $booking->id) }}" method="post"
                class="rounded-xl border border-futa-orange/50 bg-futa-orange-soft/30 p-4">
                @csrf
                <fieldset>
                    <legend class="text-sm font-extrabold">{{ __('Profile::tickets.journey.review_title') }}</legend>
                    <p class="mt-1 text-xs text-slate-600">{{ __('Profile::tickets.journey.review_hint') }}</p>
                    <input type="hidden" name="rating" value="">
                    <div class="mt-3 flex gap-1">
                        @for($star = 1; $star <= 5; $star++)
                            <button type="button" data-review-star="{{ $star }}" aria-pressed="false"
                                aria-label="{{ __('Profile::tickets.journey.review_star', ['count' => $star]) }}"
                                class="grid size-10 place-items-center rounded focus-visible:outline-2 focus-visible:outline-futa-orange">
                                <x-heroicon-s-star class="size-7 text-slate-300" aria-hidden="true" />
                            </button>
                        @endfor
                    </div>
                </fieldset>
                <div data-review-rating-labels class="mt-1 hidden text-xs font-bold text-futa-orange" aria-live="polite">
                    @foreach(__('Profile::tickets.journey.review_rating_labels') as $rating => $label)
                        <span data-review-rating-label="{{ $rating }}" hidden>{{ $label }}</span>
                    @endforeach
                </div>
                @foreach(['positive', 'negative'] as $group)
                    <div data-review-tags="{{ $group }}" class="mt-3 hidden flex-wrap gap-1.5">
                        @foreach(__('Profile::tickets.journey.review_tags_'.$group) as $tag)
                            <button type="button" data-review-suggestion="{{ $tag }}" aria-pressed="false"
                                class="rounded-full border border-gray-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 transition hover:border-futa-orange hover:text-futa-orange aria-pressed:border-futa-orange aria-pressed:bg-futa-orange-soft aria-pressed:text-futa-orange">
                                {{ $tag }}
                            </button>
                        @endforeach
                    </div>
                @endforeach
                <label for="journey-comment-{{ $booking->id }}" class="sr-only">{{ __('Profile::tickets.journey.review_comment') }}</label>
                <input type="hidden" name="comment" value="">
                <textarea id="journey-comment-{{ $booking->id }}" data-review-comment rows="3" maxlength="300"
                    placeholder="{{ __('Profile::tickets.journey.review_comment') }}"
                    class="mt-3 w-full resize-y rounded-lg border border-gray-200 bg-white p-3 text-sm outline-none focus:border-futa-orange"></textarea>
                <p data-review-count class="text-right text-xs text-slate-500">0/300</p>
                <div class="mt-2 flex items-center justify-between gap-3">
                    <p data-review-error class="text-xs font-semibold text-red-700" role="alert"></p>
                    <button type="submit" disabled class="rounded-full bg-futa-orange px-4 py-2 text-xs font-bold text-white hover:bg-futa-orange-dark disabled:cursor-not-allowed disabled:opacity-50">
                        {{ __('Profile::tickets.journey.review_submit') }}
                    </button>
                </div>
            </form>
        @elseif($stage === 'arrived' && ! $arrivalConfirmed)
            <p class="rounded-xl border border-gray-200 bg-slate-50 p-3 text-xs text-slate-600">{{ __('Profile::tickets.journey.review_pending') }}</p>
        @endif

        <section aria-labelledby="journey-events-title">
            <h3 id="journey-events-title" class="text-xs font-extrabold uppercase tracking-wide">{{ __('Profile::tickets.journey.events') }}</h3>
            <ol class="mt-4 text-sm">
                @foreach($events as $event)
                    <li class="grid grid-cols-[1.25rem_minmax(0,1fr)] gap-3 pb-5 last:pb-0" @if(in_array($event['state'], ['current', 'error'], true)) aria-current="step" @endif>
                        <div class="relative flex justify-center" aria-hidden="true">
                            @unless($loop->last)<span class="absolute top-3 -bottom-1 w-px bg-gray-200"></span>@endunless
                            <span @class([
                                'relative z-10 mt-1 size-3 shrink-0 rounded-full border-2',
                                'border-white bg-red-500 ring-4 ring-red-100' => $event['state'] === 'error',
                                'border-white bg-futa-green ring-4 ring-green-100' => $event['state'] === 'current',
                                'border-gray-300 bg-gray-300' => $event['state'] === 'past',
                                'border-gray-300 bg-white' => $event['state'] === 'future',
                            ])></span>
                        </div>
                        <div class="min-w-0">
                            <p @class([
                                'font-bold leading-5',
                                'text-red-700' => $event['state'] === 'error',
                                'text-futa-green' => $event['state'] === 'current',
                                'text-gray-900' => $event['state'] === 'past',
                                'text-gray-400' => $event['state'] === 'future',
                            ])>{{ $event['title'] }}</p>
                            @if($event['detail'])
                                <p @class(['mt-0.5 text-xs leading-4', 'text-slate-400' => $event['state'] === 'future', 'text-slate-500' => $event['state'] !== 'future'])>{{ $event['detail'] }}</p>
                            @endif
                            @if($event['date'])
                                <p @class(['mt-0.5 text-xs leading-4', 'text-slate-400' => $event['state'] === 'future', 'text-slate-500' => $event['state'] !== 'future'])>
                                    {{ $event['state'] === 'future' ? __('Profile::tickets.journey.expected_time') : __('Profile::tickets.journey.recorded_time') }}:
                                    {{ $event['date'] }}
                                </p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

        </div>
        <div class="min-w-0 space-y-5">
        <section aria-labelledby="journey-info-title">
            <h3 id="journey-info-title" class="text-xs font-extrabold uppercase tracking-wide">{{ __('Profile::tickets.journey.trip_info') }}</h3>
            <dl class="mt-3 grid grid-cols-2 gap-x-5 gap-y-3 border-t border-gray-100 pt-4 text-xs">
                <div>
                    <dt class="text-xs text-slate-500">{{ __('Profile::tickets.columns.route') }}</dt>
                    <dd class="mt-1 font-bold">{{ $booking->origin_city }} → {{ $booking->destination_city }}</dd>
                    @if($booking->destination_station)
                        <p class="mt-0.5 text-[11px] text-slate-500">{{ __('Profile::tickets.journey.dropoff') }}: {{ $booking->destination_station }}</p>
                    @endif
                </div>
                <div><dt class="text-xs text-slate-500">{{ __('Profile::tickets.journey.vehicle') }}</dt><dd class="mt-1 font-semibold">{{ __('Profile::tickets.journey.vehicle_type.'.$booking->bus_type) }} · {{ $booking->license_plate }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Profile::tickets.journey.departure') }}</dt><dd class="mt-1 font-semibold">{{ $departure->format('d/m/Y H:i') }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Profile::tickets.journey.arrival_expected') }}</dt><dd class="mt-1 font-semibold">{{ $arrival->format('d/m/Y H:i') }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Profile::tickets.journey.pickup') }}</dt><dd class="mt-1 font-semibold">{{ $booking->origin_station ?: $booking->origin_city }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Profile::tickets.journey.count_and_seats') }}</dt><dd class="mt-1 font-semibold">{{ $booking->seat_count }} · {{ $tickets->pluck('seat_code')->filter()->join(', ') ?: '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Profile::tickets.columns.payment') }}</dt><dd class="mt-1 font-semibold">{{ __('Profile::tickets.payment_status.'.($payment?->status ?? 'unpaid')) }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Profile::tickets.columns.amount') }}</dt><dd class="mt-1 font-bold text-futa-orange">{{ __('Profile::tickets.currency', ['amount' => number_format((float) $booking->total_amount, 0, ',', '.')]) }}</dd></div>
            </dl>
        </section>

        @if($tickets->isNotEmpty())
            <section aria-labelledby="journey-tickets-title">
                <h3 id="journey-tickets-title" class="text-xs font-extrabold uppercase tracking-wide">{{ __('Profile::tickets.journey.tickets') }}</h3>
                <div class="mt-3 space-y-2">
                    @foreach($tickets as $ticket)
                        <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-3">
                            @if(isset($qrCodes[$ticket->ticket_code]))
                                <img src="{{ $qrCodes[$ticket->ticket_code] }}" alt="{{ __('Profile::tickets.journey.qr_alt', ['code' => $ticket->ticket_code]) }}"
                                    class="size-24 shrink-0 rounded-lg border border-gray-200 bg-white p-1">
                            @else
                                <span class="grid size-24 shrink-0 place-items-center rounded-lg bg-gray-50 text-slate-400"><x-heroicon-o-ticket class="size-8" aria-hidden="true" /></span>
                            @endif
                            <div class="min-w-0 space-y-0.5 text-sm">
                                <p class="text-slate-600">{{ __('Profile::tickets.journey.ticket_and_seat', ['seat' => $ticket->seat_code ?: '—']) }}</p>
                                <p class="break-all font-bold text-futa-orange">{{ $ticket->ticket_code }}</p>
                                @if(isset($qrCodes[$ticket->ticket_code]) && $ticket->status === 'active')
                                    <p class="text-xs leading-5 text-slate-600">{{ __('Profile::tickets.journey.show_qr_to_staff') }}</p>
                                @else
                                    <p class="text-xs text-slate-500">{{ __('Profile::tickets.journey.ticket_status.'.$ticket->status) }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @elseif($demoQrCode ?? null)
            <section aria-labelledby="journey-demo-qr-title" class="rounded-xl border border-dashed border-futa-orange/40 bg-futa-orange-soft/30 p-3">
                <h3 id="journey-demo-qr-title" class="text-xs font-extrabold text-futa-orange">{{ __('Profile::tickets.journey.demo_qr_title') }}</h3>
                <div class="mt-2 flex items-center gap-3">
                    <img src="{{ $demoQrCode }}" alt="{{ __('Profile::tickets.journey.demo_qr_alt') }}"
                        class="size-22 shrink-0 rounded-lg border border-gray-200 bg-white p-1">
                    <p class="text-xs leading-5 text-slate-600">{{ __('Profile::tickets.journey.demo_no_ticket') }}</p>
                </div>
            </section>
        @elseif($isDemo)
            <p class="rounded-xl border border-dashed border-gray-200 p-3 text-xs text-slate-500">{{ __('Profile::tickets.journey.demo_no_ticket') }}</p>
        @endif
        </div>
        </div>
    </div>

    <footer class="flex flex-wrap items-center justify-end gap-2 border-t border-gray-100 px-5 py-3.5">
        <a href="tel:19006067" class="inline-flex min-h-10 items-center gap-2 rounded-full border border-gray-200 px-4 text-sm font-semibold text-futa-green hover:border-futa-green">
            <x-heroicon-o-phone class="size-4" aria-hidden="true" />1900 6067
        </a>
        @if($inDialog)
            <button type="button" data-history-close class="min-h-10 rounded-full bg-futa-orange px-5 text-sm font-bold text-white hover:bg-futa-orange-dark">{{ __('Profile::tickets.journey.close') }}</button>
        @else
            <a href="{{ route('profile.tickets.index') }}" class="inline-flex min-h-10 items-center rounded-full bg-futa-orange px-5 text-sm font-bold text-white hover:bg-futa-orange-dark">{{ __('Profile::tickets.back_to_history') }}</a>
        @endif
    </footer>
</article>
