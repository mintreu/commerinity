<?php

namespace Mintreu\LaravelCommerinity\Models;


use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Mintreu\LaravelCommerinity\Casts\SaleActionTypeCast;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;
use Mintreu\Toolkit\Traits\HasPackageModelFactory;
use Mintreu\Toolkit\Traits\HasUnique;

class Sale extends Model
{

    use HasPackageModelFactory,HasUnique;

    protected $fillable = [
        'name',
        'uuid',
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
        //'discount_amount' => LaravelMoneyCast::class,
        'action_type'   => SaleActionTypeCast::class
    ];


    protected static function booted()
    {
        static::creating(function ($record){
            $record->setUniqueCode('uuid',6);
        });
        parent::booted();
    }



    /**
     * Generic polymorphic relation to targets.
     */
    public function targets():MorphToMany
    {
        $targetModel = config('laravel-commerinity.sales.targets')[0] ?? null;


        if (! $targetModel) {
            throw new \RuntimeException(
                "No sale target models defined in config/laravel-commerinity.php"
            );
        }

        return $this->morphedByMany(
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



    public function __call($method, $parameters)
    {
        // If method is a known relation, return it dynamically
        if ($relation = $this->resolveDynamicTargetRelation($method)) {
            return $relation;
        }

        // Otherwise, fallback to parent __call
        return parent::__call($method, $parameters);
    }

    protected function resolveDynamicTargetRelation(string $method): ?MorphToMany
    {
        // Config defined target models
        $targets = config('laravel-commerinity.sales.targets', []);

        foreach ($targets as $class) {
            $expected = \Illuminate\Support\Str::plural(strtolower(class_basename($class)));

            if ($method === $expected) {
                return $this->morphedByMany(
                    $class,
                    'target',
                    'sale_targets',
                    'sale_id',
                    'target_id'
                );
            }
        }

        return null; // no dynamic relation found
    }




}
