<?php

namespace App\Http\Requests;

use App\Models\PerEmployee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserAccessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'PER_USER_ROLE' => 'nullable|in:ADMIN,NURSE',
            'PER_USER_IS_ACTIVE' => 'required|boolean',
            'PER_EMP_CODE' => ['nullable', 'string', Rule::exists(PerEmployee::class, 'PER_EMP_CODE')],
        ];
    }
}
