<?php

namespace Domain\Promoters\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'referral_code' => $this->referral_code,
            'name' => $this->user->name ?? 'N/A',
            'email' => $this->user->email ?? 'N/A',
            'phone' => $this->user->phone ?? 'N/A',
            'commission_type' => $this->pivot->commission_type ?? null,
            'commission_value' => $this->pivot->commission_value ?? null,
            'enabled' => $this->pivot->enabled ?? $this->enabled,
            'completed_commissions_count' => $this->pivot->completedCommissionsCount,
        ];
    }
}
