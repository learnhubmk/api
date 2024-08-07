<?php

namespace App\Website\Http\Roules;

use App\Website\Service\MailboxLayerService;
use Closure;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Validation\ValidationRule;

class MailboxValidEmail implements ValidationRule
{
    public MailboxLayerService $mailboxLayerService;

    public function __construct(MailboxLayerService $mailboxLayerService)
    {
        $this->mailboxLayerService = $mailboxLayerService;
    }

    /**
     * @throws GuzzleException
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $result = $this->mailboxLayerService->check($value);

        if (! isset($result['format_valid']) || ! $result['format_valid'] || ! isset($result['smtp_check']) || ! $result['smtp_check']) {
            $fail('The email address is not valid.');
        }
    }
}
