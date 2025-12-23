<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Recruitment\JobApplication;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasJobApplications
{
    public function jobApplications(): MorphMany
    {
        return $this->morphMany(JobApplication::class, 'applicant');
    }

    public function hasAppliedTo(int $recruitmentId): bool
    {
        return $this->jobApplications()
            ->where('recruitment_id', $recruitmentId)
            ->exists();
    }

    public function getApplicationFor(int $recruitmentId): ?JobApplication
    {
        return $this->jobApplications()
            ->where('recruitment_id', $recruitmentId)
            ->first();
    }

    public function getPendingApplicationsCount(): int
    {
        return $this->jobApplications()->pending()->count();
    }

    public function getAcceptedApplicationsCount(): int
    {
        return $this->jobApplications()
            ->where('status', 'accepted')
            ->count();
    }
}
