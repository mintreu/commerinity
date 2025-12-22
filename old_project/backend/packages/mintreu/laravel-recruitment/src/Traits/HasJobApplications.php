<?php

namespace Mintreu\LaravelRecruitment\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Mintreu\LaravelRecruitment\Models\JobApplication;

trait HasJobApplications
{

    /**
     * Get all job applications related to the model.
     */
    public function jobApplications(): MorphMany
    {
        return $this->morphMany(JobApplication::class, 'applicant');
    }


    public function applications()
    {
        return $this->jobApplications();
    }


    /**
     * Helper to check if the model has applied for a specific Naukri.
     */
    public function hasAppliedFor($recruitmentId): bool
    {
        return $this->jobApplications()->where('recruitment_id', $recruitmentId)->exists();
    }


}
