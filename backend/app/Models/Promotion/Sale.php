<?php

namespace App\Models\Promotion;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;

class Sale extends Model
{
    /** @use HasFactory<\Database\Factories\Promotion\SaleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'starts_from',
        'ends_till',
        'status',
        'condition_type',
        'conditions',
        'end_other_rules',
        'action_type',
        'discount_amount',
        'sort_order',
    ];

    protected $casts = [
        'conditions' => 'array',
        'discount_amount' => LaravelMoneyCast::class,
    ];



    /**
     * Generic polymorphic relation to targets.
     */
    public function targets(): MorphToMany
    {
        $targetModel = config('laravel-store.sales.targets')[0] ?? null;

        if (! $targetModel) {
            throw new \RuntimeException(
                "No sale target models defined in config/laravel-store.php"
            );
        }

        return $this->morphToMany(
            $targetModel,      // Related model
            'target',          // <-- morph name, matches "morphs('target')"
            'sale_targets',    // Pivot table
            'sale_id',         // FK on pivot to Sale
            'target_id'        // FK on pivot to related model
        );
    }
    public function scopeWithoutTargets(Builder $query): Builder
    {
        return $query->whereDoesntHave('targets');
    }




    /**
     * Scope: sales for a specific target model instance.
     */
    public function scopeForTarget(Builder $query, Model $target): Builder
    {
        return $query->whereHas('targets', function ($q) use ($target) {
            $q->where('target_type', get_class($target))
                ->where('target_id', $target->getKey());
        });
    }

    /**
     * Scope: sales for a specific target type (class).
     */
    public function scopeForTargetType(Builder $query, string $targetClass): Builder
    {
        return $query->whereHas('targets', function ($q) use ($targetClass) {
            $q->where('target_type', $targetClass);
        });
    }




    /**
     * Get the Sale Products in the sale.
     */
    public function sale_products(): HasMany
    {
        return $this->hasMany(SaleProduct::class);
    }








}
