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
        Schema::create('products__nomenclatures', function (Blueprint $table) {
            $table->id();
            $table->decimal('quantity', 12, 2);
            $table->decimal('price', 12, 2); // Добавляем столбец price
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('nomenclature_id')->constrained('nomenclatures')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products__nomenclatures');
    }
};
