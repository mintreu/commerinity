<x-filament-panels::page>
    <div x-data="{ tab: 'overview' }" class="space-y-6">
        <div class="flex flex-wrap items-center gap-2">
            <x-filament::button color="gray" size="sm" x-on:click="tab = 'overview'">
                Overview
            </x-filament::button>
            <x-filament::button color="gray" size="sm" x-on:click="tab = 'periods'">
                Period Breakdown
            </x-filament::button>
            @if ($this->isAdvisor)
                <x-filament::button color="gray" size="sm" x-on:click="tab = 'leaders'">
                    Team Leaders
                </x-filament::button>
            @endif
            <x-filament::button color="gray" size="sm" x-on:click="tab = 'children'">
                Child Team
            </x-filament::button>
        </div>

        <div x-show="tab === 'overview'" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-filament::section>
                <div class="text-sm text-gray-500">User Type</div>
                <div class="text-lg font-semibold">{{ $this->overview['user_type'] ?? '-' }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">Direct Referrals</div>
                <div class="text-lg font-semibold">{{ number_format((int) ($this->overview['direct_referrals'] ?? 0)) }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">Team Size</div>
                <div class="text-lg font-semibold">{{ number_format((int) ($this->overview['team_size'] ?? 0)) }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">Active Subscription</div>
                <div class="text-lg font-semibold">{{ ($this->overview['active_subscription'] ?? false) ? 'Yes' : 'No' }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">Personal Sales (All Time)</div>
                <div class="text-lg font-semibold">{{ \App\Services\MoneyService::format((int) ($this->overview['personal_sales'] ?? 0)) }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">Team Sales (All Time)</div>
                <div class="text-lg font-semibold">{{ \App\Services\MoneyService::format((int) ($this->overview['team_sales'] ?? 0)) }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">Commission Earned</div>
                <div class="text-lg font-semibold">{{ \App\Services\MoneyService::format((int) ($this->overview['commission_earned'] ?? 0)) }}</div>
            </x-filament::section>
            <x-filament::section>
                <x-filament::link :href="$this->overview['children_page_url'] ?? '#'" icon="heroicon-o-users">
                    Open Full Child Team Page
                </x-filament::link>
            </x-filament::section>
        </div>

        <div x-show="tab === 'periods'" class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Period</th>
                        <th class="px-4 py-3 font-semibold">Personal Sales</th>
                        <th class="px-4 py-3 font-semibold">Team Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->periodBreakdown as $row)
                        <tr class="border-t border-gray-200 dark:border-white/10">
                            <td class="px-4 py-3">{{ $row['label'] }}</td>
                            <td class="px-4 py-3">{{ $row['personal_sales_formatted'] }}</td>
                            <td class="px-4 py-3">{{ $row['team_sales_formatted'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($this->isAdvisor)
            <div x-show="tab === 'leaders'" class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Team Leader</th>
                            <th class="px-4 py-3 font-semibold">Leader ID</th>
                            <th class="px-4 py-3 font-semibold">Team Size</th>
                            <th class="px-4 py-3 font-semibold">Current Week</th>
                            <th class="px-4 py-3 font-semibold">Last Week</th>
                            <th class="px-4 py-3 font-semibold">Current Month</th>
                            <th class="px-4 py-3 font-semibold">Last Month</th>
                            <th class="px-4 py-3 font-semibold">Past 6 Months</th>
                            <th class="px-4 py-3 font-semibold">Past 1 Year</th>
                            <th class="px-4 py-3 font-semibold">All Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->leaderBreakdown as $leader)
                            <tr class="border-t border-gray-200 dark:border-white/10">
                                <td class="px-4 py-3">{{ $leader['team_leader'] }}</td>
                                <td class="px-4 py-3">{{ $leader['team_leader_uuid'] }}</td>
                                <td class="px-4 py-3">{{ number_format((int) $leader['team_size']) }}</td>
                                <td class="px-4 py-3">{{ $leader['current_week_sales_formatted'] ?? \App\Services\MoneyService::format(0) }}</td>
                                <td class="px-4 py-3">{{ $leader['last_week_sales_formatted'] ?? \App\Services\MoneyService::format(0) }}</td>
                                <td class="px-4 py-3">{{ $leader['current_month_sales_formatted'] ?? \App\Services\MoneyService::format(0) }}</td>
                                <td class="px-4 py-3">{{ $leader['last_month_sales_formatted'] ?? \App\Services\MoneyService::format(0) }}</td>
                                <td class="px-4 py-3">{{ $leader['past_6_months_sales_formatted'] ?? \App\Services\MoneyService::format(0) }}</td>
                                <td class="px-4 py-3">{{ $leader['past_1_year_sales_formatted'] ?? \App\Services\MoneyService::format(0) }}</td>
                                <td class="px-4 py-3">{{ $leader['all_time_sales_formatted'] ?? \App\Services\MoneyService::format(0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-4 text-gray-500">No originated team leaders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        <div x-show="tab === 'children'" class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 font-semibold">User ID</th>
                        <th class="px-4 py-3 font-semibold">Name</th>
                        <th class="px-4 py-3 font-semibold">Mobile</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->childTeamPreview as $child)
                        <tr class="border-t border-gray-200 dark:border-white/10">
                            <td class="px-4 py-3">{{ $child['uuid'] }}</td>
                            <td class="px-4 py-3">{{ $child['name'] }}</td>
                            <td class="px-4 py-3">{{ $child['mobile'] }}</td>
                            <td class="px-4 py-3">{{ $child['type'] }}</td>
                            <td class="px-4 py-3">{{ $child['status'] }}</td>
                            <td class="px-4 py-3">{{ $child['joined_at'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-gray-500">No direct child team members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
