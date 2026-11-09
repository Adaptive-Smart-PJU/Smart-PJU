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
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lamp_id')->constrained('lamps')->cascadeOnDelete();
            $table->string('gateway_id')->default('GW-TELU-01');
            $table->decimal('lux_value', 8, 2)->default(0.00);
            $table->boolean('activity_detected')->default(false);
            $table->decimal('voltage_volt', 6, 2)->default(220.00);
            $table->decimal('current_ampere', 6, 3)->default(0.000);
            $table->decimal('power_watt', 8, 2)->default(0.00);
            $table->string('power_status')->default('OFF');
            $table->unsignedTinyInteger('intensity_percent')->default(0);
            $table->timestamp('recorded_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
