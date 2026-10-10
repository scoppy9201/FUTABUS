<div class="booking-page__captcha-backdrop" x-show="captchaOpen" x-transition.opacity x-cloak
    role="presentation">
    <div class="booking-page__captcha-dialog" role="dialog" aria-modal="true"
        aria-labelledby="booking-captcha-title">
        <h2 id="booking-captcha-title" class="sr-only">{{ __('core::booking.captcha_title') }}</h2>
        <div class="booking-page__captcha-image" x-ref="captchaImage">
            <button type="button" class="booking-page__captcha-refresh"
                @click="resetCaptcha()" aria-label="{{ __('core::booking.captcha_refresh') }}"
                title="{{ __('core::booking.captcha_refresh') }}">
                <x-heroicon-o-arrow-path class="size-5" aria-hidden="true" />
            </button>
            <img class="booking-page__captcha-scene"
                :src="captchaImages[captchaImageIndex]" alt="" aria-hidden="true">
            <img class="booking-page__captcha-hole" src="{{ asset('icons/captcha/hole.svg') }}"
                alt="" aria-hidden="true" :style="{ left: captchaTarget + 'px', top: captchaTop + 'px' }">
            <div class="booking-page__captcha-piece"
                :style="{ left: captchaOffset + 'px', top: captchaTop + 'px' }">
                <img :src="captchaImages[captchaImageIndex]" alt="" aria-hidden="true"
                    :style="{ width: captchaImageWidth + 'px', height: captchaImageHeight + 'px',
                        left: -captchaTarget + 'px', top: -captchaTop + 'px' }">
            </div>
        </div>
        <div class="booking-page__captcha-track" x-ref="captchaTrack"
            :class="{ 'is-complete': captchaVerified }">
            <span x-text="captchaVerified
                ? @js(__('core::booking.captcha_success'))
                : @js(__('core::booking.captcha_prompt'))"></span>
            <button type="button" class="booking-page__captcha-handle" x-ref="captchaHandle"
                :style="{ left: captchaSliderOffset + 'px' }"
                @pointerdown.prevent="startCaptcha($event)"
                @pointermove.prevent="moveCaptcha($event)"
                @pointerup.prevent="finishCaptcha()"
                @pointercancel="captchaDragging = false; setCaptchaSlider(0)"
                @keydown.right.prevent="setCaptchaSlider(captchaSliderOffset + 10)"
                @keydown.left.prevent="setCaptchaSlider(captchaSliderOffset - 10)"
                @keydown.enter.prevent="verifyCaptcha()"
                role="slider" aria-valuemin="0" aria-valuemax="100"
                :aria-valuenow="Math.round(captchaOffset / Math.max(1, captchaImageWidth - 52) * 100)"
                aria-label="{{ __('core::booking.captcha_prompt') }}">
                <x-heroicon-o-arrow-right class="size-5" aria-hidden="true" />
            </button>
        </div>
        <p class="booking-page__captcha-error" x-show="captchaError" x-cloak role="alert">
            {{ __('core::booking.captcha_retry') }}
        </p>
    </div>
</div>
