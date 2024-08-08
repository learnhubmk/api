<?php

namespace App\Website\Http\Requests;

use App\Website\Http\Roules\MailboxValidEmail;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

class StoreNewsletterSubscriberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Resolve the MailboxValidEmail dependency from the container
        $mailboxValidEmail = app(MailboxValidEmail::class);

        return [
            'first_name' => ['required', 'string', 'max:50'],
            'email' => [
                'required',
                'email:filter',
                Rule::when(app()->isProduction(), $mailboxValidEmail),
            ],
            'cf-turnstile-response' => [
                new RequiredIf(function () {
                    return app()->isProduction();
                }),
                Rule::when(app()->isProduction(), [
                    'string',
                    Rule::turnstile(),
                ]),
            ],
        ];
    }
}
