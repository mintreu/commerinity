<?php

namespace Mintreu\LaravelRecruitment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mintreu\LaravelGeokit\Models\Address;
use Mintreu\LaravelGeokit\Traits\HasAddress;
use Mintreu\LaravelRecruitment\Casts\JobApplicationStatusCast;
use Mintreu\LaravelRecruitment\Database\Factories\JobApplicationFactory;
use Mintreu\LaravelTransaction\Traits\HasTransaction;
use Mintreu\Toolkit\Traits\HasUnique;

class JobApplication extends Model
{
    /** @use HasFactory<JobApplicationFactory> */
    use HasFactory, HasUnique, HasTransaction, HasAddress;

    const TRANSACTION_AMOUNT_VALUE = 'amount';

    protected $fillable = [
        'uuid',
        'guardian_name',
        'educations',
        'skills',
        'experiences',
        'is_paid',
        'amount',
        'reference_name',
        'reference_contact',
        'submitted_at',
        'applicant_id',
        'applicant_type',
        'status',
        'status_feedback',
        'address_id',
        'recruitment_id'
    ];

    protected $casts = [
        'status' => JobApplicationStatusCast::class,
        'submitted_at' => 'datetime',
        'is_paid' => 'boolean',
        'amount' => 'integer',
        'educations' => 'array',
        'skills' => 'array',
        'experiences' => 'array',
    ];

    protected static function booted()
    {
        static::creating(fn($form) =>
        $form->setUniqueCode('uuid', 16, 'APP-' . now()->format('ym') . '-')
        );
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function recruitment(): BelongsTo
    {
        return $this->belongsTo(Recruitment::class, 'recruitment_id', 'id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }

    /**
     * Polymorphic relation to applicant (User, Candidate, etc.)
     */
    public function applicant(): MorphTo
    {
        return $this->morphTo();
    }

    public function customer(): MorphTo
    {
        return $this->applicant();
    }
}
