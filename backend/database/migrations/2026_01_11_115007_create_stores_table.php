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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Owner
            $table->string('name');
            $table->text('description')->nullable();
            
            // --- NEW COLUMNS FOR CUSTOMIZATION ---
            $table->string('banner')->nullable();
            $table->string('layout')->default('grid-4')->nullable();
            $table->string('primary_color')->default('#9333ea')->nullable();
            $table->json('featured_products')->nullable(); // Stores IDs like [1, 5, 8]
            // -------------------------------------
            
            $table->string('status')->default('active'); // active, suspended
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};