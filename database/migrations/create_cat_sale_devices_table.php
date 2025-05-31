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
        Schema::create('cat_sale_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cat_sale_id')->constrained()->onDelete('cascade');
            $table->string('cat_device_identifiant');
            $table->foreign('cat_device_identifiant')->references('identifiant')->on('cat_devices')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cat_sale_devices');
    }
};