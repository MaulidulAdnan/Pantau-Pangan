<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('market_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 12, 2);
            $table->enum('stock_status', ['available', 'scarce', 'out_of_stock'])->default('available');
            $table->boolean('is_suspicious')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'market_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
