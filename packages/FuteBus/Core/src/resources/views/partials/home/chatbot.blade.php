@php($chatUser = auth()->user())
<div class="futa-chat"
    data-futa-chat
    data-message-url="{{ route('api.v1.chatbot.messages.store') }}"
    data-contact-url="{{ route('api.v1.contact-messages.store') }}"
    data-locale="{{ app()->getLocale() }}"
    data-error="{{ __('core::chatbot.error') }}"
    data-unavailable="{{ __('core::chatbot.unavailable') }}"
    data-typing="{{ __('core::chatbot.typing') }}"
    data-feedback-label="{{ __('core::chatbot.actions.complaint') }}"
    data-feedback-error="{{ __('core::chatbot.feedback.error') }}"
    data-feedback-required="{{ __('core::chatbot.feedback.required') }}"
    data-feedback-success="{{ __('core::chatbot.feedback.success') }}">
    <section id="futa-chat-panel" class="futa-chat__panel" data-chat-panel aria-label="{{ __('core::chatbot.panel_label') }}" aria-hidden="true" hidden>
        <header class="futa-chat__header">
            <div class="futa-chat__identity">
                <span class="futa-chat__avatar"><x-heroicon-s-chat-bubble-left-right class="size-5" /></span>
                <span><strong>{{ __('core::chatbot.name') }}</strong><small><i></i> {{ __('core::chatbot.online') }}</small></span>
            </div>
            <button class="futa-chat__close" type="button" data-chat-close aria-label="{{ __('core::chatbot.close') }}">
                <x-heroicon-o-x-mark class="size-6" />
            </button>
        </header>

        <div class="futa-chat__messages" data-chat-messages aria-live="polite" aria-relevant="additions">
            <div class="futa-chat__intro">
                <div><strong>{{ __('core::chatbot.greeting') }}</strong><p>{{ __('core::chatbot.intro') }}</p></div>
            </div>
            <div class="futa-chat__suggestions" data-chat-suggestions>
                @foreach (__('core::chatbot.suggestions') as $suggestion)
                    <button type="button" data-chat-prompt="{{ $suggestion }}">{{ $suggestion }}</button>
                @endforeach
            </div>

            <form class="futa-chat__feedback"
                data-chat-feedback-form
                data-contact-url="{{ $chatUser ? route('profile.feedback.chatbot.store') : route('api.v1.contact-messages.store') }}"
                data-mode="{{ $chatUser ? 'member' : 'guest' }}"
                data-csrf="{{ csrf_token() }}"
                hidden>
                <h3>{{ __('core::chatbot.feedback.title') }}</h3>
                @if ($chatUser)
                    <input type="hidden" name="category" value="other">
                    <input type="hidden" name="urgency" value="complaint">
                    <input type="hidden" name="truth_confirm" value="1">
                @else
                    <input type="hidden" name="department" value="futabus">
                @endif
                <label>{{ __('core::chatbot.feedback.name') }}
                    <input name="{{ $chatUser ? 'contact_name' : 'name' }}" maxlength="120" autocomplete="name" value="{{ $chatUser?->name }}" required>
                </label>
                <label>{{ __('core::chatbot.feedback.email') }}
                    <input name="{{ $chatUser ? 'contact_email' : 'email' }}" type="email" maxlength="255" autocomplete="email" value="{{ $chatUser?->email }}" @required(! $chatUser)>
                </label>
                <label>{{ __('core::chatbot.feedback.phone') }}
                    <input name="{{ $chatUser ? 'contact_phone' : 'phone' }}" type="tel" maxlength="20" autocomplete="tel" required>
                </label>
                <label>{{ __('core::chatbot.feedback.subject') }}
                    <input name="subject" maxlength="{{ $chatUser ? 150 : 255 }}" minlength="5" required>
                </label>
                <label>{{ __('core::chatbot.feedback.message') }}
                    <textarea name="message" rows="3" minlength="{{ $chatUser ? 20 : 1 }}" maxlength="{{ $chatUser ? 3000 : 5000 }}" required></textarea>
                </label>
                <p class="futa-chat__feedback-error" data-feedback-error role="alert" hidden></p>
                <button type="submit">{{ __('core::chatbot.feedback.submit') }}</button>
            </form>
        </div>

        <form class="futa-chat__composer" data-chat-form>
            <label class="sr-only" for="futa-chat-input">{{ __('core::chatbot.input_label') }}</label>
            <input id="futa-chat-input" data-chat-input type="text" maxlength="1000" placeholder="{{ __('core::chatbot.placeholder') }}" autocomplete="off" data-error="{{ __('core::chatbot.error') }}">
            <button type="submit" aria-label="{{ __('core::chatbot.send') }}" class="futa-chat__send"><x-heroicon-s-paper-airplane class="size-5" /></button>
        </form>
        <p class="futa-chat__note">{{ __('core::chatbot.notice') }}</p>
    </section>

    <button type="button" class="futa-chat__launcher" data-chat-open aria-label="{{ __('core::chatbot.open') }}" aria-expanded="false" aria-controls="futa-chat-panel">
        <x-heroicon-s-chat-bubble-left-right class="size-6" />
        <span class="futa-chat__tooltip">{{ __('core::chatbot.tooltip') }}</span>
    </button>
</div>
