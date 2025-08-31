<?php

namespace App\Models\Traits;

use App\Models\Lifecycle\Stage;
use Carbon\Carbon;
use function PHPUnit\Framework\throwException;

trait HasLifecycle
{
    /**
     * Get the next lifecycle stage for upgrading/continuing the subscription.
     *
     * @return Stage|null
     */
    public function getNextLifecycleStage(): ?Stage
    {
        try {
            // Latest existing membership
            $existingMembership = $this->memberships()->latest()->first();

            // CASE 1: No subscription exists
            if (!$existingMembership) {
                return Stage::with([
                    'levels' => fn($query) => $query
                        ->where('status', true)
                        ->orderBy('id', 'asc')
                        ->limit(1)
                ])
                    ->where('status', true)
                    ->orderBy('id', 'asc')
                    ->first();
            }

            // CASE 2: Subscription exists and not expired
            if ($existingMembership->expires_at > Carbon::now() && $existingMembership->is_paid) {
                $level = $existingMembership->level()->first();

                if ($level) {
                    $stage = $level->stage()->first();
                    if ($stage) {
                        $stage->load([
                            'levels' => fn($query) => $query->where('id', $level->id)->limit(1)
                        ]);
                    }
                    return $stage;
                }
            }

            // CASE 3: Subscription expired
            $currentLevel = $existingMembership->level()->first();
            if ($currentLevel) {
                $nextLevel = $currentLevel->nextRecord();

                if (!$nextLevel) {
                    return null; // No next level exists
                }

                $stage = $nextLevel->stage()->first();
                if ($stage) {
                    $stage->load([
                        'levels' => fn($query) => $query->where('id', $nextLevel->id)->limit(1)
                    ]);
                }
                return $stage;
            }

            return null;
        }catch (\Throwable $t)
        {
            report($t);
        }
    }
}
