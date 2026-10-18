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
        Schema::create('glucose_readings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->integer('glucose_level');
            $table->timestamp('measured_at');
            $table->string('reading_context');
            $table->text('notes')->nullable();
            $table->string('source')->default('web');
            $table->timestamps();
            $table->index('measured_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('glucose_readings');
    }
};
