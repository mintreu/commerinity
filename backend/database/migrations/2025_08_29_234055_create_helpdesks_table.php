<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('helpdesks', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();   // use as ticket id
            $table->string('title');
            $table->text('description');
            $table->string('priority');
            $table->foreignId('topic_id')->constrained('helpdesk_topics')->cascadeOnUpdate()->cascadeOnDelete();
            $table->morphs('authorable');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesks');
    }
};
