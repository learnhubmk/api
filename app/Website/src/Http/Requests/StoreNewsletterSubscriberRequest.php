<?php

namespace App\Website\Http\Requests;

use App\Website\Http\Roules\MailboxValidEmail;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

class StoreNewsletterSubscriberRequest extends FormRequest
{
    protected MailboxValidEmail $mailboxValidEmail;

    public function __construct(MailboxValidEmail $mailboxValidEmail)
    {
        parent::__construct();
        $this->mailboxValidEmail = $mailboxValidEmail;
    }

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
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email:filter', $this->mailboxValidEmail],
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
