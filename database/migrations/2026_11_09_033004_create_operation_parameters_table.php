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
        Schema::create('operation_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lamp_id')->nullable()->constrained('lamps')->cascadeOnDelete();
            $table->string('profile_name');
            $table->decimal('lux_threshold', 8, 2)->default(25.00);
            $table->decimal('hysteresis_lux', 8, 2)->default(15.00);
            $table->unsignedTinyInteger('dim_intensity_percent')->default(30);
            $table->unsignedTinyInteger('bright_intensity_percent')->default(100);
            $table->string('energy_saving_start_time', 10)->default('22:00');
            $table->string('energy_saving_end_time', 10)->default('05:00');
            $table->boolean('is_custom')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamp('synced_to_gateway_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_parameters');
    }
};
