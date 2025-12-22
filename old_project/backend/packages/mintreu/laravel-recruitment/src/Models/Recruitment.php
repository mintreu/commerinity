<?php

namespace Mintreu\LaravelRecruitment\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;

use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelRecruitment\Casts\RecruitmentTypeCast;
use Mintreu\Toolkit\Casts\PublishableStatusCast;
use Mintreu\Toolkit\Traits\HasPackageModelFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Recruitment extends Model implements HasMedia
{
    /** @use HasFactory<\Mintreu\LaravelRecruitment\Database\Factories\RecruitmentFactory> */
    use HasPackageModelFactory,InteractsWithMedia;


    protected $fillable = [
        'name',
        'url',
        'description',
        'role',
        'location',
        'type',
        'vacancy',
        'open_date',
        'close_date',
        'is_payable',
        'fees',
        'status',
        'status_feedback',
    ];

    protected $casts = [
        'open_date' => 'date',
        'close_date' => 'date',
        'is_payable' => 'boolean',
        'fees' => 'integer',
        'type' => RecruitmentTypeCast::class,
        'status' => PublishableStatusCast::class
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('displayImage')
            ->useFallbackUrl('https://placehold.co/600x400?text='.$this->name);

        $this->addMediaCollection('infoPdf')
            ->useFallbackUrl('https://s2.q4cdn.com/175719177/files/doc_presentations/Placeholder-PDF.pdf');

    }

    public function isPayable()
    {
        return $this->is_payable;
    }

    public function getFees(bool $formatted = false)
    {
        return $formatted ? LaravelMoney::format($this->fees) : $this->fees;
    }

    /**
     * Scope a query to include only records within the open/close date range.
     */
    public function scopeWithinDate($query): Builder
    {
        return $query->whereDate('open_date', '<=', now())
            ->whereDate('close_date', '>=', now());
    }



    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class,'recruitment_id','id');
    }

}
