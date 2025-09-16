<?php

namespace Mintreu\Toolkit\Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

trait HasRecordNavigator
{
    /**
     * Scope to get the next records based on the given column.
     *
     * @param Builder $query
     * @param Model $model
     * @param string $column
     * @return Builder
     */
    public function scopeNextRecord(Builder $query, Model $model, string $column = 'id'): Builder
    {
        return $query->where($column, '>', $model->{$column})
            ->orderBy($column, 'asc');
    }

//    /**
//     * Instance method to get the next record.
//     *
//     * @param string $column
//     * @return Model|null
//     */
//    public function nextRecord(string $column = 'id'): ?Model
//    {
//        return static::nextRecord(static::query(), $this, $column)->first();
//    }

    /**
     * Scope to get the previous records based on the given column.
     *
     * @param Builder $query
     * @param Model $model
     * @param string $column
     * @return Builder
     */
    public function scopePrevRecord(Builder $query, Model $model, string $column = 'id'): Builder
    {
        return $query->where($column, '<', $model->{$column})
            ->orderBy($column, 'desc');
    }

//    /**
//     * Instance method to get the previous record.
//     *
//     * @param string $column
//     * @return Model|null
//     */
//    public function prevRecord(string $column = 'id'): ?Model
//    {
//        return static::prevRecord(static::query(), $this, $column)->first();
//    }



    public function nextRecord(string $column = 'id'): ?Model
    {
        return static::query()->nextRecord($this, $column)->first();
    }

    public function prevRecord(string $column = 'id'): ?Model
    {
        return static::query()->prevRecord($this, $column)->first();
    }



    /**
     * Scope for first record with optional query modifications.
     *
     * @param Builder $query
     * @param string $column
     * @param Closure|null $modifyQuery
     * @return Builder
     */
    public function scopeFirstRecord(Builder $query, string $column = 'id', ?Closure $modifyQuery = null): Builder
    {
        $query = $query->orderBy($column, 'asc');
        if ($modifyQuery) {
            $query = $modifyQuery($query);
        }
        return $query;
    }

    /**
     * Static method to get first record or query builder.
     *
     * @param string $column
     * @param Closure|null $modifyQuery
     * @param bool $returnQuery
     * @return Model|\Illuminate\Database\Eloquent\Builder|null
     */
    public static function firstRecord(string $column = 'id', ?Closure $modifyQuery = null, bool $returnQuery = false)
    {
        $query = static::query()->orderBy($column, 'asc');
        if ($modifyQuery) {
            $query = $modifyQuery($query);
        }
        return $returnQuery ? $query : $query->first();
    }

    /**
     * Scope for last record with optional query modifications.
     *
     * @param Builder $query
     * @param string $column
     * @param Closure|null $modifyQuery
     * @return Builder
     */
    public function scopeLastRecord(Builder $query, string $column = 'id', ?Closure $modifyQuery = null): Builder
    {
        $query = $query->orderBy($column, 'desc');
        if ($modifyQuery) {
            $query = $modifyQuery($query);
        }
        return $query;
    }

    /**
     * Static method to get last record or query builder.
     *
     * @param string $column
     * @param Closure|null $modifyQuery
     * @param bool $returnQuery
     * @return Model|\Illuminate\Database\Eloquent\Builder|null
     */
    public static function lastRecord(string $column = 'id', ?Closure $modifyQuery = null, bool $returnQuery = false)
    {
        $query = static::query()->orderBy($column, 'desc');
        if ($modifyQuery) {
            $query = $modifyQuery($query);
        }
        return $returnQuery ? $query : $query->first();
    }
}
