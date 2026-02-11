<?php

namespace App\Filament\Resources\Ecommerce\Products\Schemas\Traits;

use App\Casts\ProductTypeCast;
use App\Models\Ecommerce\Filter;
use App\Models\Ecommerce\FilterGroup;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;

trait HasFilterConfiguration
{
    /**
     * Cache filter groups to avoid duplicate queries.
     *
     * @var array<int, FilterGroup>
     */
    protected array $filterGroupCache = [];

    protected function filterConfigurationSection(Get $get, Set $set): array
    {
        $filterGroupId = $get('filter_group_id');
        $productType = $get('type');

        $isConfigurable = $productType === ProductTypeCast::CONFIGURABLE->value;

        return array_merge([
            Select::make('filter_group_id')
                ->label(__('Filter Group'))
                ->relationship('filterGroup', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->helperText(__('Defines the filters that apply to this product.'))
                ->afterStateUpdated(function (?int $state, Get $get, Set $set): void {
                    $this->resetFilterOptions($state, (string) $get('type'), $set);
                })
                ->live(),
        ], $this->buildFilterFields($filterGroupId, $isConfigurable));
    }

    /**
     * Build each filter select based on the selected filter group.
     *
     * @param int|null $filterGroupId
     * @param bool $isConfigurable
     * @return array<int, Select>
     */
    protected function buildFilterFields(?int $filterGroupId, bool $isConfigurable): array
    {
        $filterGroup = $this->resolveFilterGroup($filterGroupId);

        if (! $filterGroup) {
            return [];
        }

        return $filterGroup->filters
            ->map(fn (Filter $filter) => Select::make("filter_options.{$filter->id}")
                ->label($filter->name)
                ->options($filter->options->pluck('value', 'id')->toArray())
                ->preload()
                ->live()
                ->multiple($isConfigurable)
                ->required($filter->is_required)
                ->default(fn (?Model $record) => $this->resolveFilterDefaultValue($filter->id, $record, $isConfigurable))
            )
            ->toArray();
    }

    protected function resolveFilterGroup(?int $id): ?FilterGroup
    {
        if (! $id) {
            return null;
        }

        return $this->filterGroupCache[$id] ??= FilterGroup::with(['filters.options'])->find($id);
    }

    protected function resetFilterOptions(?int $filterGroupId, string $productType, Set $set): void
    {
        $filterGroup = $this->resolveFilterGroup($filterGroupId);
        if (! $filterGroup) {
            return;
        }

        $isConfigurable = $productType === ProductTypeCast::CONFIGURABLE->value;

        foreach ($filterGroup->filters as $filter) {
            $key = "filter_options.{$filter->id}";
            $set($key, $isConfigurable ? [] : null);
        }
    }

    protected function resolveFilterDefaultValue(int $filterId, ?Model $record, bool $isConfigurable): array|string|null
    {
        if (! $record) {
            return $isConfigurable ? [] : null;
        }

        $selected = $record->filterOptions()
            ->where('filter_id', $filterId)
            ->pluck('id')
            ->map(fn ($value) => (string) $value)
            ->toArray();

        if ($isConfigurable) {
            return $selected;
        }

        return isset($selected[0]) ? $selected[0] : null;
    }
}
