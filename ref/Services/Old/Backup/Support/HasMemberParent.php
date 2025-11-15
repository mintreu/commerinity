<?php

namespace App\Services\Backup\Support;

use App\Models\Enums\AuthStatusCast;
use App\Models\User;

trait HasMemberParent
{


    private  $availableParents = null;


    protected function hasAvailableParent(string $ref_code): bool
    {
        $this->availableParents = $this->getAllAvailableParents($ref_code);
        return $this->availableParents->isEmpty(); // Return true if no available parents
    }

    protected function getAvailableParent(string $ref_code,bool $next = false)
    {
        if (is_null($this->availableParents))
        {
            $this->availableParents = $this->getAllAvailableParents($ref_code);
        }
        return ($next) ? $this->availableParents->first() : $this->availableParents->skip(1)->first();
    }

//    protected function getAllAvailableParents(string $ref_code)
//    {
//        $parentModel = User::firstWhere('ref_code', $ref_code);
//        if ($parentModel)
//        {
//            $this->availableParents =  $parentModel?->descendantsAndSelf()
//                ->where('status','=',AuthStatusCast::ACTIVE)
//                ->where('direct_children_count', '<', 5);
//        }
//        return $this->availableParents;
//    }



    protected function getAllAvailableParents(string $ref_code)
    {
        $parentModel = User::firstWhere('ref_code', $ref_code);

        // Return an empty collection if no parent model is found
        if (!$parentModel) {
            return collect();
        }

        return $parentModel->descendantsAndSelf()
            ->where('status', '=', AuthStatusCast::ACTIVE)
            ->withCount('children') // Eager load count of children
            ->having('children_count', '<', 5) // Filter based on count
            ->get(); // Retrieve results
    }


}
