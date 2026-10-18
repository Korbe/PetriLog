<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewsletterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            // subscribers = nur angemeldete User; all = Service-Mitteilung an alle (auch Abgemeldete)
            'audience' => 'nullable|in:subscribers,all',
            // Rechtlich nur für echte Service-Informationen zulässig – muss ausdrücklich bestätigt werden
            'service_confirmed' => 'exclude_unless:audience,all|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'service_confirmed.accepted' => 'Bitte bestätige, dass es sich um eine Service-Mitteilung ohne Werbung handelt.',
        ];
    }

    public function isServiceMessage(): bool
    {
        return $this->input('audience') === 'all';
    }
}
