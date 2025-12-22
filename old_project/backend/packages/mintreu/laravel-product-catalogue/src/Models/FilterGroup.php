<?php

namespace Mintreu\LaravelProductCatalogue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterGroup extends Model
{
    /** @use HasFactory<\Database\Factories\FilterGroupFactory> */
    use HasFactory;


    protected $fillable = [
        'name'
    ];

    public function filters()
    {
        return $this->belongsToMany(Filter::class, 'filter_group_mappings', 'filter_group_id', 'filter_id');
    }


    public function products()
    {
        return $this->hasMany(Product::class,'filter_group_id');
    }

}
