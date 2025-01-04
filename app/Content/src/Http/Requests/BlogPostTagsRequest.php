<?php

namespace App\Content\Http\Requests;

use App\Framework\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogPostTagsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole(RoleName::ADMIN) || $this->user()->hasRole(RoleName::CONTENT_MANAGER);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'name' => ['required', 'string', 'max:255', Rule::unique('blog_post_tags', 'name')->whereNull('deleted_at')],
        ];
    }
}
