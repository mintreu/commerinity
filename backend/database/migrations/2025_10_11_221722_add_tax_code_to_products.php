<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('tax_code_id')
                ->nullable()
                ->constrained('tax_codes')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->boolean('is_tax_inclusive')
                ->default(false)
                ->comment('Indicates if the product price includes tax');

            $table->boolean('is_exempted')
                ->default(false)
                ->comment('Indicates if this product is exempt from tax');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['tax_code_id']);
            $table->dropColumn(['tax_code_id', 'is_tax_inclusive', 'is_exempted']);
        });
    }
};
