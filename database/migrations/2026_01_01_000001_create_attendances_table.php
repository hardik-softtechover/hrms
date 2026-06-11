<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->unsignedInteger('total_break_seconds')->default(0);
            $table->unsignedInteger('staffing_seconds')->default(0); // computed on checkout
            $table->timestamps();

            $table->unique(['user_id', 'work_date']);
        });

        Schema::create('attendance_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->timestamp('break_in_at');
            $table->timestamp('break_out_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_breaks');
        Schema::dropIfExists('attendances');
    }
};
