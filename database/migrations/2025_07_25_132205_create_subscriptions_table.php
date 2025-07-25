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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // contoh: Netflix, Spotify
            $table->decimal('amount', 15, 2);
            $table->enum('cycle', ['harian', 'mingguan', 'bulanan', 'tahunan'])->default('bulanan');
            $table->date('start_date');
            $table->date('next_payment_date')->nullable(); // untuk pengingat selanjutnya
            $table->text('description')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
