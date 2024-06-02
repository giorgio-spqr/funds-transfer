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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_account_id')
                ->nullable()
                ->default(null);
            $table->unsignedBigInteger('target_account_id')
                ->nullable()
                ->default(null);
            $table->float('rate', 6)
                ->nullable(false);
            $table->float('sent_amount', 6)
                ->nullable(false);
            $table->float('deducted_amount', 6)
                ->nullable(false);
            $table->string('currency')->nullable(false);
            $table->timestamps();

            $table->foreign('source_account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('set null');

            $table->foreign('target_account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
