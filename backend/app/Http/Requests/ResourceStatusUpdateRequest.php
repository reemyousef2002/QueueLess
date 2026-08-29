<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResourceStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isVolunteerOrStaff() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'resourceType' => ['required', 'string', 'max:50'],
            'availability' => ['required', Rule::in(['available', 'limited', 'depleted'])],
        ];
    }
}
