<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('districts')) {
            Schema::table('districts', function (Blueprint $table) {
                if (! Schema::hasColumn('districts', 'state_id')) {
                    $table->foreignId('state_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('states')
                        ->cascadeOnUpdate()
                        ->cascadeOnDelete();
                }
                if (! Schema::hasColumn('districts', 'name')) {
                    $table->string('name')->nullable()->after('state_id');
                }
                if (! Schema::hasColumn('districts', 'slug')) {
                    $table->string('slug')->nullable()->after('name');
                }
                if (! Schema::hasColumn('districts', 'code')) {
                    $table->string('code', 20)->nullable()->after('slug');
                }
                if (! Schema::hasColumn('districts', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('code');
                }
            });
        }

        if (Schema::hasTable('blocks') && ! Schema::hasColumn('blocks', 'district_id')) {
            Schema::table('blocks', function (Blueprint $table) {
                $table->foreignId('district_id')
                    ->nullable()
                    ->after('district_name')
                    ->constrained('districts')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('addresses') && ! Schema::hasColumn('addresses', 'district_id')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->foreignId('district_id')
                    ->nullable()
                    ->after('block_id')
                    ->constrained('districts')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('addresses') && Schema::hasColumn('addresses', 'district_id')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->dropConstrainedForeignId('district_id');
            });
        }

        if (Schema::hasTable('blocks') && Schema::hasColumn('blocks', 'district_id')) {
            Schema::table('blocks', function (Blueprint $table) {
                $table->dropConstrainedForeignId('district_id');
            });
        }
    }
};

