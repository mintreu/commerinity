<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Casts\OrderStatusCast;
use App\Models\Ecommerce\Order;
use App\Models\Membership\UserSubscription;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class AdminBusinessVolumeChart extends ChartWidget
{
    protected ?string $heading = 'Business Volume (Last 12 Months)';

    protected ?string $maxHeight = '320px';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '120s';

    protected function getData(): array
    {
        $start = now()->startOfMonth()->subMonths(11);
        $labels = collect(range(0, 11))
            ->map(fn (int $index): string => $start->copy()->addMonths($index)->format('M Y'))
            ->all();

        $monthKeys = collect(range(0, 11))
            ->mapWithKeys(fn (int $index): array => [
                $start->copy()->addMonths($index)->format('Y-m') => $index,
            ]);

        $orderSums = Order::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(total) as amount")
            ->whereIn('status', [
                OrderStatusCast::CONFIRMED->value,
                OrderStatusCast::PROCESSING->value,
                OrderStatusCast::SHIPPED->value,
                OrderStatusCast::DELIVERED->value,
                OrderStatusCast::COMPLETED->value,
            ])
            ->where('created_at', '>=', $start)
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->pluck('amount', 'ym');

        $subscriptionSums = UserSubscription::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(amount) as amount")
            ->where('is_paid', true)
            ->where('created_at', '>=', $start)
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->pluck('amount', 'ym');

        $orderData = array_fill(0, 12, 0.0);
        foreach ($orderSums as $ym => $amount) {
            if (! $monthKeys->has($ym)) {
                continue;
            }
            $orderData[$monthKeys[$ym]] = round(((int) $amount) / 100, 2);
        }

        $subscriptionData = array_fill(0, 12, 0.0);
        foreach ($subscriptionSums as $ym => $amount) {
            if (! $monthKeys->has($ym)) {
                continue;
            }
            $subscriptionData[$monthKeys[$ym]] = round(((int) $amount) / 100, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Order Volume (INR)',
                    'data' => $orderData,
                    'borderColor' => '#7c3aed',
                    'backgroundColor' => 'rgba(124, 58, 237, 0.15)',
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Subscription Volume (INR)',
                    'data' => $subscriptionData,
                    'borderColor' => '#0891b2',
                    'backgroundColor' => 'rgba(8, 145, 178, 0.15)',
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return 'Rs ' + value;
                            }
                        }
                    }
                }
            }
        JS);
    }
}
