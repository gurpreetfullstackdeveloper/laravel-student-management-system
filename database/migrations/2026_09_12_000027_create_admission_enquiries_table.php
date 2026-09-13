<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('student_name');
            $table->string('parent_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('class_applied_for');
            $table->text('message')->nullable();
            $table->enum('status', ['new', 'contacted', 'converted', 'rejected'])->default('new');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('admission_enquiries'); }
};