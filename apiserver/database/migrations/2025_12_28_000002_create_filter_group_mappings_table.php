<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filter_group_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filter_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filter_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['filter_group_id', 'filter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filter_group_mappings');
    }
};
