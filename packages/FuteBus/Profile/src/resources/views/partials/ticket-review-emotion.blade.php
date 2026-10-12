<span class="ticket-review-emotion-icon" aria-hidden="true">
    <img src="{{ asset('animations/trip-review-emotions/rating-'.$emotionRating.'.svg') }}"
        width="96" height="96" alt="" decoding="async">
    <canvas data-review-rive="{{ $emotionRating }}" data-review-rive-src="{{ asset('animations/trip-review-emotions.riv') }}"
        width="96" height="96" aria-hidden="true"></canvas>
</span>
