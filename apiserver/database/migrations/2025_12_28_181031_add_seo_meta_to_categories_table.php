<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Note: Categories table already has 'meta_data' JSON column
     * We'll add 'seo_meta' for dedicated SEO data
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->json('seo_meta')->nullable()->after('desc')->comment('SEO meta data: title, description, keywords, og_image, etc.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('seo_meta');
        });
    }
};
