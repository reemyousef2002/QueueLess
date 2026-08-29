<?php

namespace App\Http\Requests;

use App\Models\PriorityRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PriorityRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in([
                PriorityRegistration::ELDERLY,
                PriorityRegistration::DISABILITY,
                PriorityRegistration::PREGNANT,
                PriorityRegistration::MOTHER_WITH_CHILDREN,
            ])],
        ];
    }
}
