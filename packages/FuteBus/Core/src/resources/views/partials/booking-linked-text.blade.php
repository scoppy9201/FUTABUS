@foreach (preg_split('/(1900\s*(?:6067|6918)|CMND và GIẤY BÁO THI|ID card and EXAM ADMISSION NOTICE)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE) as $part)
    @if (preg_match('/^1900\s*(?:6067|6918)$/', $part))
        <a class="booking-page__hotline" href="tel:{{ preg_replace('/\D+/', '', $part) }}">{{ $part }}</a>
    @elseif (in_array($part, ['CMND và GIẤY BÁO THI', 'ID card and EXAM ADMISSION NOTICE'], true))
        <strong class="booking-page__terms-emphasis">{{ $part }}</strong>
    @else
        {{ $part }}
    @endif
@endforeach
