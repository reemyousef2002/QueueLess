<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DistributionPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        // Only the create (POST) request actually needs these fields —
        // update (PUT/PATCH) accepts a partial payload, and destroy
        // (DELETE, which just deactivates the point) sends no body at all.
        $requiresFields = $this->isMethod('post');

        return [
            'name' => [$requiresFields ? 'required' : 'sometimes', 'string', 'max:150'],
            'type' => [$requiresFields ? 'required' : 'sometimes', Rule::in([
                'clinic', 'government_office', 'university_office',
                'bakery', 'water_point', 'community_kitchen',
            ])],
            'address' => ['nullable', 'string', 'max:255'],
            'contactPhone' => ['nullable', 'string', 'max:20'],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }
}
