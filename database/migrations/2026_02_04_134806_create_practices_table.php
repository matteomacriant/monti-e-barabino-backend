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
        Schema::create('practices', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->integer('year');
            $table->integer('month');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('note')->nullable();
            
            // Wireframe search fields
            $table->string('client')->nullable();
            $table->string('supplier')->nullable();
            $table->string('order_number')->nullable();
            $table->string('supplier_order_number')->nullable();
            $table->string('ddt_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->integer('invoice_year')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practices');
    }
};
