<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('helpdesk_faqs', function (Blueprint $table) {
            if (Schema::hasColumn('helpdesk_faqs', 'helpful_count')) {
                $table->dropColumn('helpful_count');
            }

            if (Schema::hasColumn('helpdesk_faqs', 'not_helpful_count')) {
                $table->dropColumn('not_helpful_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('helpdesk_faqs', function (Blueprint $table) {
            if (! Schema::hasColumn('helpdesk_faqs', 'helpful_count')) {
                $table->integer('helpful_count')->default(0);
            }

            if (! Schema::hasColumn('helpdesk_faqs', 'not_helpful_count')) {
                $table->integer('not_helpful_count')->default(0);
            }
        });
    }
};
