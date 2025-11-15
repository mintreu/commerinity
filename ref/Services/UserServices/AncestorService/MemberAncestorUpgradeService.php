<?php

namespace App\Services\UserServices\AncestorService;

use App\Models\Enums\AuthStatusCast;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MemberAncestorUpgradeService
{

    protected User $subscriber;

    /**
     * Subscriber
     * @param User $record
     */
    public function __construct(User $record)
    {
        $this->subscriber = $record;
        $this->subscriber->loadMissing([
            'ancestors' => function ($query) {
                $query->where('status', AuthStatusCast::SUBSCRIBED);
            },
            'ancestors.level',
            'descendants' => function ($query) {
                $query->where('status',AuthStatusCast::SUBSCRIBED)->count();
            }
        ]);
    }


    public static function make(User $record): static
    {
        return new static($record);
    }




    public function checkAndUpgrade():void
    {

        // First Check Subscriber has a parent
        $parentUser = $this->subscriber->parent;

        if ($parentUser)
        {
            // Upgrade Allowed
            $this->upgradeAncestorLevel();

        }

    }



    protected function upgradeAncestorLevel()
    {
        // Fetch ancestors related to the current subscriber
        $ancestors = $this->subscriber->ancestors;

        if ($ancestors->count()) {
            // Group ancestors by level_id
            $groupedAncestors = $ancestors->groupBy('level_id');

            // Loop through the grouped ancestors to check their levels
            foreach ($groupedAncestors as $ancestorMembers) {
                // Assuming you need to get the first ancestor's level in each group
                $groupLevelModel = $ancestorMembers->first()->level;

                // Fetch the team member limit for the current level
                $currentTeamLimit = $groupLevelModel->team_member_limit;

                // Check if any ancestor has exceeded the team member limit
                foreach ($ancestorMembers as $member) {
                    // Count the number of descendants for this ancestor
                    $currentMemberTeamMemberCount = $member->descendants->count();

                    // Add one for the current subscriber being added
                    $currentMemberTeamMemberCount++;

                    // If the number of descendants + current subscriber exceeds the team limit, upgrade the ancestor
                    if ($currentTeamLimit < $currentMemberTeamMemberCount) {
                        // Upgrade the ancestor level
                        $this->upgradeLevelForAncestor($member);
                    }
                }
            }
        }
    }

    protected function upgradeLevelForAncestor($ancestor)
    {
        // Logic to upgrade the ancestor to the next level
        // For example, updating the ancestor's `level_id` to the next level
        $nextLevel = $ancestor->level->nextRecord(); // next Level is defined in the Level model

        if ($nextLevel) {
            $ancestor->level_id = $nextLevel->id;
            $ancestor->save();

            // Optionally, log or notify that the level has been upgraded

            // Send Filament DB Notification

            Notification::make()
                ->title('Congratulations! 🎉')
                ->body('You’ve leveled up! Keep up the great work and continue leading the way! 🌟')
                ->success()
                ->sendToDatabase($ancestor);




        }
    }






}
