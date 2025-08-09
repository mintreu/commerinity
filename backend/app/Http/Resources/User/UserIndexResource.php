<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            //'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'email_verified' => !is_null($this->email_verified_at),
            'mobile_verified' => !is_null($this->mobile_verified_at),
            'referral_code' => $this->referral_code,
            'parent' => $this->parent_id,
            'hasParent' => !is_null($this->parent_id),
            'gender' => $this->gender,
            'dob' => $this->dob,
            'type' => $this->type->getLabel(),
            'status' => $this->status->getLabel(),
            'status_feedback' => $this->status_feedback,
            'avatar' => $this->getFirstMediaUrl('avatarImage'),

        ];
    }
}
