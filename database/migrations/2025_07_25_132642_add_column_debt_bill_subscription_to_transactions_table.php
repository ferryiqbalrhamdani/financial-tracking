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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('debt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bill_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['debt_id']);
            $table->dropForeign(['bill_id']);
            $table->dropForeign(['subscription_id']);
            $table->dropColumn(['debt_id', 'bill_id', 'subscription_id']);
        });
    }
};
