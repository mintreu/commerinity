<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Ecommerce\Filter;
use App\Models\Ecommerce\FilterGroup;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class FilterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds filters, filter options, filter groups from JSON data files.
     * Data files should be at:
     * - storage/app/private/data/filters/filter.json
     * - storage/app/private/data/filters/filter-group.json
     */
    public function run(): void
    {
        $groupBag = $this->getFromStorage('private/data/filters/filter-group.json');
        if (! $groupBag || ! is_array($groupBag)) {
            throw new Exception('Failed to load filter-group.json or invalid format');
        }

        $attributeBag = $this->getFromStorage('private/data/filters/filter.json');
        if (! $attributeBag) {
            throw new Exception('Failed to load filter.json');
        }

        $finalAttributes = new Collection;

        // Create Filters and their options
        foreach ($attributeBag as $data) {
            $filter = Filter::updateOrCreate([
                'name' => $data->display_name,
                'is_required' => $data->required
            ]);
            $finalAttributes->push($filter);

            // Create Options
            if (isset($data->options) && is_array($data->options)) {
                foreach ($data->options as $value) {
                    $filter->options()->updateOrCreate(
                        [
                            'value' => $value->display_name,
                        ],
                        [
                            'swatch_value' => $value->swatch_type ?? null,
                        ]
                    );
                }
            }
        }

        $this->command->info("Created {$finalAttributes->count()} filters with options");

        // Create Filter Groups and attach filters
        foreach ($groupBag as $group) {
            $filterGroup = FilterGroup::updateOrCreate([
                'name' => $group->name,
            ]);

            // Find filters by name and attach them to the group
            $filters = $finalAttributes->filter(function ($filter) use ($group) {
                return in_array($filter->name, $group->attributes);
            })->pluck('id');

            $filterGroup->filters()->sync($filters);

            $this->command->info("Created filter group '{$group->name}' with {$filters->count()} filters");
        }
    }

    protected function getFromStorage(string $path): mixed
    {
        $fullPath = storage_path('app/'.$path);

        if (! file_exists($fullPath)) {
            throw new Exception("File not found: {$path}. Full path: {$fullPath}");
        }

        $content = file_get_contents($fullPath);
        if (! $content) {
            throw new Exception("Empty file: {$path}");
        }

        $decoded = json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in {$path}: ".json_last_error_msg());
        }

        return $decoded;
    }
}

