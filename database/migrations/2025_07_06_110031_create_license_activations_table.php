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
        Schema::create('license_activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_key_id')->constrained()->cascadeOnDelete();
            $table->string('device_id'); // ID unik dari mesin/perangkat klien
            $table->string('ip_address')->nullable();
            $table->timestamps();

            // Mencegah satu kunci diaktifkan di perangkat yang sama lebih dari sekali
            $table->unique(['license_key_id', 'device_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_activations');
    }
};
