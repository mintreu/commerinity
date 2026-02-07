<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->primary(['product_id', 'category_id']);
        });

        $this->syncExistingProducts();
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }

    private function syncExistingProducts(): void
    {
        $chunkSize = 500;

        DB::table('products')
            ->whereNotNull('category_id')
            ->orderBy('id')
            ->chunk($chunkSize, function ($products) {
                $insert = [];
                foreach ($products as $product) {
                    $insert[] = [
                        'product_id' => $product->id,
                        'category_id' => $product->category_id,
                        'is_primary' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (! empty($insert)) {
                    DB::table('product_categories')->insert($insert);
                }
            });
    }
};
