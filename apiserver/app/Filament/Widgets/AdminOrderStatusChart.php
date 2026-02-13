<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Casts\OrderStatusCast;
use App\Models\Ecommerce\Order;
use Filament\Widgets\ChartWidget;

final class AdminOrderStatusChart extends ChartWidget
{
    protected ?string $heading = 'Order Status Mix (Last 30 Days)';

    protected ?string $maxHeight = '320px';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '120s';

    protected function getData(): array
    {
        $statusCounts = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('status')
            ->pluck('total', 'status');

        $orderedStatuses = [
            OrderStatusCast::PENDING,
            OrderStatusCast::CONFIRMED,
            OrderStatusCast::PROCESSING,
            OrderStatusCast::SHIPPED,
            OrderStatusCast::DELIVERED,
            OrderStatusCast::COMPLETED,
            OrderStatusCast::CANCELLED,
            OrderStatusCast::REFUNDED,
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($orderedStatuses as $status) {
            $count = (int) ($statusCounts[$status->value] ?? 0);
            if ($count <= 0) {
                continue;
            }

            $labels[] = $status->getLabel();
            $data[] = $count;
            $colors[] = match ($status) {
                OrderStatusCast::PENDING => '#6b7280',
                OrderStatusCast::CONFIRMED => '#f59e0b',
                OrderStatusCast::PROCESSING => '#3b82f6',
                OrderStatusCast::SHIPPED => '#06b6d4',
                OrderStatusCast::DELIVERED => '#10b981',
                OrderStatusCast::COMPLETED => '#059669',
                OrderStatusCast::CANCELLED => '#ef4444',
                OrderStatusCast::REFUNDED => '#8b5cf6',
            };
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
