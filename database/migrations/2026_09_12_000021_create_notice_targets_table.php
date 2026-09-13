<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notice_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained()->cascadeOnDelete();
            $table->enum('audience_type', ['all', 'class', 'role']);
            $table->unsignedBigInteger('audience_id')->nullable();
            $table->unique(['notice_id', 'audience_type', 'audience_id'], 'notice_target_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_targets');
    }
};