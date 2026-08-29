<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CounterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaffOrAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        // store() (POST) needs a label; update() (PUT) may only be flipping
        // isActive — same "required-on-create-only" gotcha as
        // DistributionPointRequest.
        return [
            'label' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:30'],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }
}
