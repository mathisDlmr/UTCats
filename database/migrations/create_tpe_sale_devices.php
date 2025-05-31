<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tpe_sale_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cat_sale_id')->constrained()->onDelete('cascade');
            $table->string('tpe_device_identifiant');
            $table->foreign('tpe_device_identifiant')->references('identifiant')->on('tpe_devices')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tpe_sale_devices');
    }
};