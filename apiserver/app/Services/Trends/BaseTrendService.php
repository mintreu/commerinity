<?php

declare(strict_types=1);

namespace App\Services\Trends;

use Carbon\Carbon;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;

/**
 * BaseTrendService - Common functionality for all trend services
 *
 * Provides:
 * - Date range parsing
 * - Interval configuration
 * - Response formatting for Chart.js
 * - Label formatting
 */
abstract class BaseTrendService
{
    /**
     * Available intervals for trend aggregation
     */
    protected const INTERVALS = ['minute', 'hour', 'day', 'week', 'month', 'year'];

    /**
     * Default interval for each period type
     */
    protected const PERIOD_INTERVALS = [
        'today' => 'hour',
        'yesterday' => 'hour',
        'week' => 'day',
        'month' => 'day',
        'quarter' => 'week',
        'year' => 'month',
        'custom' => 'day',
    ];

    /**
     * Parse period string into start and end dates
     *
     * @return array{start: Carbon, end: Carbon}
     */
    protected function parsePeriod(
        string $period,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        return match ($period) {
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'yesterday' => [
                'start' => now()->subDay()->startOfDay(),
                'end' => now()->subDay()->endOfDay(),
            ],
            'week' => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
            ],
            'last_week' => [
                'start' => now()->subWeek()->startOfWeek(),
                'end' => now()->subWeek()->endOfWeek(),
            ],
            'month' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
            ],
            'last_month' => [
                'start' => now()->subMonth()->startOfMonth(),
                'end' => now()->subMonth()->endOfMonth(),
            ],
            'quarter' => [
                'start' => now()->startOfQuarter(),
                'end' => now()->endOfQuarter(),
            ],
            'year' => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear(),
            ],
            'last_year' => [
                'start' => now()->subYear()->startOfYear(),
                'end' => now()->subYear()->endOfYear(),
            ],
            'custom' => [
                'start' => $startDate ? Carbon::parse($startDate)->startOfDay() : now()->subMonth()->startOfDay(),
                'end' => $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay(),
            ],
            default => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
            ],
        };
    }

    /**
     * Get the best interval for a given period
     */
    protected function getIntervalForPeriod(string $period, ?string $interval = null): string
    {
        if ($interval && in_array($interval, self::INTERVALS, true)) {
            return $interval;
        }

        return self::PERIOD_INTERVALS[$period] ?? 'day';
    }

    /**
     * Format trend data for Chart.js response
     *
     * @param  Collection<TrendValue>  $data
     * @param  string  $label  Dataset label
     * @param  string|null  $color  Dataset color
     * @return array{labels: array, datasets: array}
     */
    protected function formatForChart(
        Collection $data,
        string $label,
        ?string $color = null,
        ?string $borderColor = null
    ): array {
        $labels = $data->map(fn (TrendValue $value) => $this->formatLabel($value->date))->toArray();
        $values = $data->map(fn (TrendValue $value) => $value->aggregate)->toArray();

        $dataset = [
            'label' => $label,
            'data' => $values,
        ];

        if ($color) {
            $dataset['backgroundColor'] = $color;
        }

        if ($borderColor) {
            $dataset['borderColor'] = $borderColor;
        }

        return [
            'labels' => $labels,
            'datasets' => [$dataset],
        ];
    }

    /**
     * Format multiple datasets for Chart.js
     *
     * @param  array<string, Collection>  $datasetsMap  Map of label => trend data
     * @param  array<string, array{bg: string, border: string}>  $colors  Map of label => colors
     */
    protected function formatMultipleForChart(array $datasetsMap, array $colors = []): array
    {
        $labels = [];
        $datasets = [];

        foreach ($datasetsMap as $label => $data) {
            if (empty($labels) && $data->isNotEmpty()) {
                $labels = $data->map(fn (TrendValue $value) => $this->formatLabel($value->date))->toArray();
            }

            $dataset = [
                'label' => $label,
                'data' => $data->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
            ];

            if (isset($colors[$label])) {
                $dataset['backgroundColor'] = $colors[$label]['bg'];
                $dataset['borderColor'] = $colors[$label]['border'];
            }

            $datasets[] = $dataset;
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    /**
     * Format date label based on format
     */
    protected function formatLabel(string $date): string
    {
        $carbon = Carbon::parse($date);

        // Detect format based on date string pattern
        if (preg_match('/^\d{4}$/', $date)) {
            return $date; // Year only
        }

        if (preg_match('/^\d{4}-\d{2}$/', $date)) {
            return $carbon->format('M Y'); // Month Year
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $carbon->format('M d'); // Month Day
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $date)) {
            return $carbon->format('H:i'); // Hour:Minute
        }

        return $carbon->format('M d, Y');
    }

    /**
     * Build response with metadata
     */
    protected function buildResponse(
        array $chartData,
        array $summary = [],
        string $period = 'month',
        string $interval = 'day'
    ): array {
        return [
            'success' => true,
            'data' => array_merge($chartData, ['summary' => $summary]),
            'meta' => [
                'period' => $period,
                'interval' => $interval,
                'generated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Convert paisa to rupees for display
     */
    protected function paisaToRupees(int $paisa): float
    {
        return $paisa / 100;
    }

    /**
     * Convert collection of paisa values to rupees
     */
    protected function convertCollectionToRupees(Collection $data): Collection
    {
        return $data->map(function (TrendValue $value) {
            return new TrendValue(
                date: $value->date,
                aggregate: $this->paisaToRupees((int) $value->aggregate)
            );
        });
    }

    /**
     * Calculate summary statistics from trend data
     *
     * @param  Collection<TrendValue>  $data
     */
    protected function calculateSummary(Collection $data): array
    {
        $values = $data->pluck('aggregate');

        return [
            'total' => $values->sum(),
            'average' => round($values->avg(), 2),
            'min' => $values->min(),
            'max' => $values->max(),
            'count' => $data->count(),
        ];
    }

    /**
     * Calculate percentage change between two values
     */
    protected function calculatePercentageChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
