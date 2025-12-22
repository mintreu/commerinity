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
        Schema::create('helpdesk_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('helpdesk_id')->constrained('helpdesks')->cascadeOnDelete();
            $table->text('message');
            $table->morphs('authorable'); // who wrote the message
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_conversations');
    }
};
