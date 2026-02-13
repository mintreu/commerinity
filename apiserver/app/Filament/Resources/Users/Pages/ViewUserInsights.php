<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Casts\CommissionStatusCast;
use App\Casts\OrderStatusCast;
use App\Casts\UserTypeCast;
use App\Filament\Resources\Users\UserResource;
use App\Models\Affiliate\AffiliateCommission;
use App\Models\Ecommerce\Order;
use App\Models\Membership\UserSubscription;
use App\Models\User;
use App\Services\MoneyService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Collection;

class ViewUserInsights extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Insights';

    protected string $view = 'filament.resources.users.pages.view-user-insights';

    /** @var array<string, array{label: string, start: Carbon|null, end: Carbon|null}> */
    public array $periodWindows = [];

    /** @var array<string, array<string, int|string>> */
    public array $periodBreakdown = [];

    /** @var array<int, array<string, int|string>> */
    public array $leaderBreakdown = [];

    /** @var array<int, array<string, mixed>> */
    public array $childTeamPreview = [];

    /** @var array<string, int|string|bool> */
    public array $overview = [];

    public bool $isMemberOrPromoter = false;

    public bool $isAdvisor = false;

    private const SALE_STATUSES = [
        OrderStatusCast::CONFIRMED->value,
        OrderStatusCast::PROCESSING->value,
        OrderStatusCast::SHIPPED->value,
        OrderStatusCast::DELIVERED->value,
        OrderStatusCast::COMPLETED->value,
    ];

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->buildInsights();
    }

    public function getSubheading(): ?string
    {
        return 'Role-based affiliate business overview with printable period breakdown.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->extraAttributes([
                    'x-on:click' => 'window.print()',
                ]),
            EditAction::make(),
        ];
    }

    private function buildInsights(): void
    {
        /** @var User $user */
        $user = $this->getRecord();
        $userType = $user->type instanceof UserTypeCast ? $user->type : UserTypeCast::tryFrom((string) $user->type);

        $this->isMemberOrPromoter = in_array($userType?->value, [UserTypeCast::MEMBER->value, UserTypeCast::PROMOTER->value], true);
        $this->isAdvisor = $userType?->value === UserTypeCast::ADVISOR->value;

        $this->periodWindows = $this->resolvePeriodWindows();

        $personalSalesAllTime = $this->sumSales([$user->id]);
        $personalCommissionAllTime = (int) AffiliateCommission::query()
            ->where('user_id', $user->id)
            ->whereNotIn('status', [CommissionStatusCast::CANCELLED->value, CommissionStatusCast::REVERSED->value])
            ->sum('net_amount');

        $downlineIds = $user->descendants()->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $teamSize = count($downlineIds);
        $teamSalesAllTime = $this->sumSales($downlineIds);

        $this->overview = [
            'user_type' => $userType?->getLabel() ?? ucfirst((string) $user->type),
            'direct_referrals' => (int) $user->children()->count(),
            'team_size' => $teamSize,
            'personal_sales' => $personalSalesAllTime,
            'team_sales' => $teamSalesAllTime,
            'commission_earned' => $personalCommissionAllTime,
            'active_subscription' => (bool) UserSubscription::query()
                ->where('user_id', $user->id)
                ->where('status', UserSubscription::STATUS_ACTIVE)
                ->where('is_paid', true)
                ->exists(),
            'children_page_url' => UserResource::getUrl('children', ['record' => $user]),
        ];

        $this->periodBreakdown = $this->buildPeriodBreakdown(
            userIds: [$user->id],
            teamUserIds: $downlineIds
        );

        $this->leaderBreakdown = [];
        if ($this->isAdvisor) {
            $this->leaderBreakdown = $this->buildAdvisorLeaderBreakdown($user);
        }

        $this->childTeamPreview = $user->children()
            ->select(['id', 'uuid', 'name', 'mobile', 'type', 'status', 'created_at'])
            ->latest()
            ->limit(25)
            ->get()
            ->map(function (User $child): array {
                $childStatus = $child->status;

                return [
                    'uuid' => (string) $child->uuid,
                    'name' => (string) $child->name,
                    'mobile' => (string) ($child->mobile ?? '-'),
                    'type' => $child->type instanceof UserTypeCast ? $child->type->getLabel() : (string) $child->type,
                    'status' => is_object($childStatus) && method_exists($childStatus, 'value')
                        ? (string) $childStatus->value
                        : (string) $childStatus,
                    'joined_at' => optional($child->created_at)?->format('d M Y'),
                ];
            })
            ->all();
    }

    /**
     * @return array<string, array{label: string, start: Carbon|null, end: Carbon|null}>
     */
    private function resolvePeriodWindows(): array
    {
        $now = now();

        return [
            'current_week' => [
                'label' => 'Current Week',
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
            'last_week' => [
                'label' => 'Last Week',
                'start' => $now->copy()->subWeek()->startOfWeek(),
                'end' => $now->copy()->subWeek()->endOfWeek(),
            ],
            'current_month' => [
                'label' => 'Current Month',
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            'last_month' => [
                'label' => 'Last Month',
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth(),
            ],
            'past_6_months' => [
                'label' => 'Past 6 Months',
                'start' => $now->copy()->subMonths(6)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'past_1_year' => [
                'label' => 'Past 1 Year',
                'start' => $now->copy()->subYear()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'all_time' => [
                'label' => 'All Time',
                'start' => null,
                'end' => null,
            ],
        ];
    }

    /**
     * @param  array<int, int>  $userIds
     */
    private function sumSales(array $userIds, ?Carbon $start = null, ?Carbon $end = null): int
    {
        if ($userIds === []) {
            return 0;
        }

        $query = Order::query()
            ->where('customerable_type', User::class)
            ->whereIn('customerable_id', $userIds)
            ->whereIn('status', self::SALE_STATUSES);

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        return (int) $query->sum('total');
    }

    /**
     * @param  array<int, int>  $userIds
     * @param  array<int, int>  $teamUserIds
     * @return array<string, array<string, int|string>>
     */
    private function buildPeriodBreakdown(array $userIds, array $teamUserIds): array
    {
        $breakdown = [];

        foreach ($this->periodWindows as $key => $window) {
            $personalSales = $this->sumSales($userIds, $window['start'], $window['end']);
            $teamSales = $this->sumSales($teamUserIds, $window['start'], $window['end']);

            $breakdown[$key] = [
                'label' => $window['label'],
                'personal_sales' => $personalSales,
                'personal_sales_formatted' => MoneyService::format($personalSales),
                'team_sales' => $teamSales,
                'team_sales_formatted' => MoneyService::format($teamSales),
            ];
        }

        return $breakdown;
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function buildAdvisorLeaderBreakdown(User $advisor): array
    {
        /** @var Collection<int, User> $teamLeaders */
        $teamLeaders = $advisor->originatedUsers()
            ->select(['id', 'uuid', 'name', 'mobile', 'type'])
            ->orderBy('name')
            ->get();

        return $teamLeaders->map(function (User $leader): array {
            $teamIds = array_values(array_unique([
                $leader->id,
                ...$leader->descendants()->pluck('id')->map(fn ($id): int => (int) $id)->all(),
            ]));

            $row = [
                'team_leader' => (string) $leader->name,
                'team_leader_uuid' => (string) $leader->uuid,
                'team_size' => max(0, count($teamIds) - 1),
                'all_time_sales' => 0,
                'all_time_sales_formatted' => MoneyService::format(0),
            ];

            foreach ($this->periodWindows as $key => $window) {
                $sales = $this->sumSales($teamIds, $window['start'], $window['end']);
                $row[$key.'_sales'] = $sales;
                $row[$key.'_sales_formatted'] = MoneyService::format($sales);
            }

            $row['all_time_sales'] = (int) $row['all_time_sales'];
            $row['all_time_sales_formatted'] = (string) ($row['all_time_sales_formatted'] ?? MoneyService::format(0));

            return $row;
        })->all();
    }
}
