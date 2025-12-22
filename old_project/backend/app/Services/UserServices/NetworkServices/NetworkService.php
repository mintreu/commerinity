<?php

namespace App\Services\UserServices\NetworkServices;



use App\Models\User;
use App\Services\UserServices\NetworkServices\Support\MemberTreeList;
use App\Services\UserServices\NetworkServices\Support\ParentPredictor;
use Illuminate\Database\Eloquent\Model;

class NetworkService
{

    protected User $record;

    public function __construct(User $record)
    {
        $this->record = $record;
    }


    public static function make(User|Model $record): static
    {
        return new static($record);
    }


    public function addToNetwork(?User $sponsor = null): void
    {
        // Fix $newMember Parent Record
        if (!is_null($this->record->parent_id))
        {
            $predictor = ParentPredictor::make($this->record,$sponsor);
            $allowedSponsor = $predictor->getSponsor();
            if ($allowedSponsor)
            {
                $this->record->update([
                    'parent_id' => $allowedSponsor->id
                ]);
            }
        }


    }


    public function getTree(): MemberTreeList
    {
        return MemberTreeList::make($this->record)
            ->withDepth(5)        // Maximum 5 levels deep
            ->withLimit(5);        // Maximum 5 children per node (5x5 matrix);
    }





    public function calculateCommission()
    {

    }

    public function qualifyForBonus(string $bonusType): bool
    {
        // Example: active subscription, minimum sales volume
        return true;
    }


    public function upgradeUplinePosition()
    {

    }

    public function disburseBonusToUpline()
    {

    }

    public function LeadershipBonusSharingToDownline()
    {

    }


    public function disburseTeamRewardToTree()
    {

    }


    public function notifyTeam()
    {

    }

    public function notifyUpline()
    {

    }

    public function notifyDownline()
    {

    }



}
