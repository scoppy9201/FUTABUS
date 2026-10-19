<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChatbotMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $messages = $this->input('messages');
        if (! is_array($messages)) {
            return;
        }

        foreach ($messages as $index => $message) {
            if (is_array($message) && is_string($message['text'] ?? null)) {
                $messages[$index]['text'] = trim($message['text']);
            }
        }

        $this->merge(['messages' => $messages]);
    }

    public function rules(): array
    {
        return [
            'locale'          => ['nullable', Rule::in(['vi', 'en'])],
            'messages'        => ['required', 'array', 'min:1', 'max:12'],
            'messages.*'      => ['required', 'array:role,text'],
            'messages.*.role' => ['required', Rule::in(['user', 'assistant'])],
            'messages.*.text' => ['required', 'string', 'max:1000'],
        ];
    }
}
