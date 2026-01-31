<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Ecommerce\SaleManager;
use Illuminate\Console\Command;

class SalesReindex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sales-reindex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild sale products for active sales';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        SaleManager::make()->reindexSaleableProducts();

        $this->info('Sales reindex completed.');

        return self::SUCCESS;
    }
}
