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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // nama tagihan, contoh: Shopee Paylater
            $table->decimal('amount', 15, 2);
            $table->date('start_date');
            $table->date('due_date');
            $table->enum('term', ['sekali', '3_bulan', '6_bulan', '12_bulan'])->default('sekali'); // jangka waktu
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
