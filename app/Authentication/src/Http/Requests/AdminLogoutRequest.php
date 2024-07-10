<?php

namespace App\Authentication\Http\Requests;

use App\Framework\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;

class AdminLogoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole(RoleName::ADMIN);
    }


}
