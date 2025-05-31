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
        Schema::create('cat_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cat_request_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['none', 'configured', 'devices_assigned', 'retrieved', 'returned'])->default('none');
            $table->text('notes')->nullable();
            
            $table->string('bde_member_pickup')->nullable()->comment('Membre BDE qui donne le matériel');
            $table->string('receiver_pickup')->nullable()->comment('Personne qui récupère le matériel');
            $table->boolean('caution_collected')->default(false)->comment('Caution récupérée');
            $table->decimal('caution_amount', 8, 2)->nullable()->comment('Montant de la caution');
            $table->timestamp('pickup_at')->nullable();
            
            $table->string('bde_member_return')->nullable()->comment('Membre BDE qui récupère');
            $table->string('returner')->nullable()->comment('Personne qui rend');
            $table->boolean('caution_returned')->default(false)->comment('Caution rendue');
            $table->timestamp('returned_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cat_sales');
    }
};