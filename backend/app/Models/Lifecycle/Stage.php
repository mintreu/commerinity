<?php

namespace App\Models\Lifecycle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;

class Stage extends Model
{
    /** @use HasFactory<\Database\Factories\Lifecycle\StageFactory> */
    use HasFactory;



    protected $fillable = [
        'name',
        'url',
        'desc',
        'base_price',
        'discount',
        'tax_percentage',
        'tax_amount',
        'price',
        'max_team_members',
        'estimated_total_joining_points',
        'estimated_total_purchasing_points',
        'estimated_total_clan_points',
        'benefits',
        'accessibility',
        'status',
        'min_per_order',
        'max_per_order',
    ];

    protected $casts = [
        'benefits' => 'array',
        'accessibility' => 'array',
        'price' => LaravelMoneyCast::class,
        'status' => 'boolean',
        'min_per_order' => 'integer',
        'max_per_order' => 'integer',
    ];


    public function levels(): HasMany
    {
        return $this->hasMany(Level::class,'stage_id','id');
    }


}
