<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrowdDensityReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isVolunteerOrStaff() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'distributionPointId' => ['required', 'uuid', Rule::exists('distribution_points', 'id')],
            'densityLevel' => ['required', Rule::in(['green', 'yellow', 'red'])],
        ];
    }
}
