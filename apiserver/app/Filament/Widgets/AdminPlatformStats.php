<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Casts\OrderStatusCast;
use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use App\Models\Ecommerce\Order;
use App\Models\Membership\UserSubscription;
use App\Models\User;
use App\Services\MoneyService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class AdminPlatformStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $totalUsers = User::query()->count();
        $activeUsers = User::query()->where('status', UserStatusCast::ACTIVE)->count();
        $memberCount = User::query()->where('type', UserTypeCast::MEMBER)->count();
        $promoterCount = User::query()->where('type', UserTypeCast::PROMOTER)->count();

        $activeSubscriptions = UserSubscription::query()
            ->where('status', UserSubscription::STATUS_ACTIVE)
            ->where('is_paid', true)
            ->count();

        $totalOrders = Order::query()->count();
        $pendingOrders = Order::query()->where('status', OrderStatusCast::PENDING->value)->count();
        $confirmedBusiness = (int) Order::query()
            ->whereIn('status', [
                OrderStatusCast::CONFIRMED->value,
                OrderStatusCast::PROCESSING->value,
                OrderStatusCast::SHIPPED->value,
                OrderStatusCast::DELIVERED->value,
                OrderStatusCast::COMPLETED->value,
            ])
            ->sum('total');

        return [
            Stat::make('Total Users', number_format($totalUsers))
                ->description('Active: '.number_format($activeUsers))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Members + Promoters', number_format($memberCount + $promoterCount))
                ->description('Members: '.number_format($memberCount).' | Promoters: '.number_format($promoterCount))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Active Subscriptions', number_format($activeSubscriptions))
                ->description('Currently paid and active')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Total Orders', number_format($totalOrders))
                ->description('Pending: '.number_format($pendingOrders))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),

            Stat::make('Business Volume', MoneyService::format($confirmedBusiness))
                ->description('Confirmed to completed orders')
                ->descriptionIcon('heroicon-m-currency-rupee')
                ->color('success'),
        ];
    }
}
