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
            'referral_code' => $this->promoter->referral_code,
            'name' => $this->promoter->user->name ?? 'N/A',
            'email' => $this->promoter->user->email ?? 'N/A',
            'phone' => $this->promoter->user->phone ?? 'N/A',
            'commission_type' => $this->commission_type,
            'commission_value' => $this->commission_value,
            'enabled' => $this->enabled,
        ];
    }
}
