<?php

namespace App\Http\Requests;

use App\Models\CommunityUpdate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommunityUpdateRequest extends FormRequest
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
            'updateType' => ['required', Rule::in([
                CommunityUpdate::RESOURCE_ARRIVED,
                CommunityUpdate::RESOURCE_DEPLETED,
                CommunityUpdate::QUEUE_PAUSED,
                CommunityUpdate::QUEUE_RESUMED,
                CommunityUpdate::OTHER,
            ])],
            'message' => ['nullable', 'string', 'max:255'],
        ];
    }
}
