<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_period_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('trimestre')->unique();
            $table->string('modo', 20)->default('GLOBAL');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('evaluation_period_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_period_setting_id')
                ->constrained('evaluation_period_settings')
                ->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['evaluation_period_setting_id', 'teacher_id'], 'eval_period_teacher_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_period_teachers');
        Schema::dropIfExists('evaluation_period_settings');
    }
};
