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
        Schema::create('lamps', function (Blueprint $table) {
            $table->id();
            $table->string('lamp_code')->unique();
            $table->string('name');
            $table->string('location');
            $table->decimal('latitude', 10, 7)->default(-6.9732000);
            $table->decimal('longitude', 10, 7)->default(107.6303000);
            $table->string('controller_device_id')->unique();
            $table->boolean('is_active')->default(true);
            $table->string('power_status')->default('OFF');
            $table->string('operation_mode')->default('ADAPTIVE');
            $table->unsignedTinyInteger('intensity_percent')->default(0);
            $table->string('connectivity_status')->default('ONLINE');
            $table->decimal('lux_value', 8, 2)->default(0.00);
            $table->boolean('activity_detected')->default(false);
            $table->decimal('voltage_volt', 6, 2)->default(220.00);
            $table->decimal('current_ampere', 6, 3)->default(0.000);
            $table->decimal('power_watt', 8, 2)->default(0.00);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamps');
    }
};
