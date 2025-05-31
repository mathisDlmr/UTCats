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
        Schema::create('cat_devices', function (Blueprint $table) {
            $table->string('identifiant')->primary()->comment('Identifiant après 80405#');
            $table->enum('etat', ['ok', 'moyen', 'hs'])
                  ->default('ok')
                  ->comment('État du terminal');
            $table->boolean('dans_malette')->default(false)->comment('Terminal présent dans la malette');
            $table->string('lieu')->nullable()->comment('Lieu où se trouve le terminal');
            $table->date('dernier_ping')->nullable()->comment('Dernier ping reçu');
            $table->text('commentaires')->nullable()->comment('Commentaires sur le terminal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cat_devices');
    }
};