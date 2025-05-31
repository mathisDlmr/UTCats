<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cat_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('asso');
            $table->string('event_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('cats_count');
            $table->integer('tpe_count');
            $table->string('lieu')->nullable();
            $table->string('lieu_autre')->nullable();
            $table->enum('connexion', ['4g', 'rhizome'])->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->boolean('ready')->default(false);
            $table->json('responsibles');
            $table->json('articles');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cat_requests');
    }
};