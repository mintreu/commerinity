<?php

namespace App\Models;

use App\Casts\NaukriEmploymentTypeCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Mintreu\Toolkit\Casts\PublishableStatusCast;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Naukri extends Model  implements  HasMedia
{
    /** @use HasFactory<\Database\Factories\NaukriFactory> */
    use HasFactory,InteractsWithMedia;


    protected $fillable = [
        'name',
        'url',
        'description',
        'role',
        'location',
        'employment_type',
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
        'fees' => 'decimal:2',
        'employment_type' => NaukriEmploymentTypeCast::class,
        'status' => PublishableStatusCast::class
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('displayImage')
            ->useFallbackUrl('https://placehold.co/600x400?text='.$this->name);

        $this->addMediaCollection('infoPdf')
            ->useFallbackUrl('https://s2.q4cdn.com/175719177/files/doc_presentations/Placeholder-PDF.pdf');

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
        return $this->hasMany(NaukriApplication::class,'naukri_id','id');
    }


}
