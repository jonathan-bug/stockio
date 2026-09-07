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
        Schema::create('kardex', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedTinyInteger('type');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('stock');
            $table->text('description')->nullable();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kardex');
    }
};
