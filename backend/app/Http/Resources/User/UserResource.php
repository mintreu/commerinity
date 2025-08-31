<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Geo\AddressResource;
use App\Http\Resources\Lifecycle\UserSubscriptionResource;
use App\Http\Resources\Transaction\KycResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends UserIndexResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request),[
            'bio' => $this->bio,
            'address' => AddressResource::make($this->whenLoaded('address')),
            'kyc'   => KycResource::make($this->whenLoaded('kyc')),
            'subscription' => UserSubscriptionResource::make($this->whenLoaded('membership'))
        ]);
    }
}
