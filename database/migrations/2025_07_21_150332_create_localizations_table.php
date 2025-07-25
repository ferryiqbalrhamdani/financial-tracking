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
        Schema::create('localizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('locale')->default('id');
            $table->string('currency')->default('IDR');
            $table->string('timezone')->default('Asia/Jakarta');
            $table->string('date_format')->default('d/m/Y');
            $table->unsignedTinyInteger('monthly_period_start_day')->default(1)->comment('Tanggal mulai periode bulanan (1-28)');
            $table->unsignedTinyInteger('monthly_period_start_month')->default(1)->comment('Bulan mulai periode bulanan (1-12)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('localizations');
    }
};
