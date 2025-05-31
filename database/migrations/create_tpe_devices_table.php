<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tpe_devices', function (Blueprint $table) {
            $table->string('identifiant')->primary();
            $table->enum('etat', ['ok', 'moyen', 'hs'])->default('ok');
            $table->boolean('disponible')->default(true);
            $table->string('lieu')->nullable();
            $table->text('commentaires')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tpe_devices');
    }
};