<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Filter extends Model
{
    /** @use HasFactory<\Database\Factories\FilterFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'is_required'
    ];

    protected $casts = [
        'is_required' => 'boolean'
    ];


    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(FilterGroup::class, 'filter_group_mappings', 'filter_id', 'filter_group_id');
    }


    public function options(): HasMany
    {
        return $this->hasMany(FilterOption::class,'filter_id','id');
    }



}
