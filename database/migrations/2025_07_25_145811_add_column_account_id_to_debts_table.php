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
        Schema::table('debts', function (Blueprint $table) {
            $table->foreignId('account_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade')
                ->before('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['account_id']);

            // Then drop the column
            $table->dropColumn('account_id');
        });
    }
};
