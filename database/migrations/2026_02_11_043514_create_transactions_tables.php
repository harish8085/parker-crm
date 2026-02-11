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
            $table->unsignedBigInteger('settlement_id');
            $table->unsignedBigInteger('user_id'); // parent channel
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('tds_amount', 15, 2);
            $table->decimal('advance_amount', 15, 2)->default(0);
            $table->decimal('net_payable', 15, 2);
            $table->string('status')->default('pending'); // pending, approved, completed
            $table->unsignedBigInteger('created_by');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamps();

            $table->foreign('settlement_id')->references('id')->on('settlements')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('completed_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('settlement_distribution_id');
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('tds', 15, 2);
            $table->decimal('net_amount', 15, 2);
            $table->decimal('advance_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('settlement_distribution_id')->references('id')->on('settlement_distributions')->onDelete('cascade');
        });

        Schema::create('transaction_bank_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('bank_account_id');
            $table->decimal('amount', 15, 2);
            $table->string('utr_number')->nullable();
            $table->string('payment_status')->nullable();
            $table->timestamps();

            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('bank_account_id')->references('id')->on('bank_data')->onDelete('cascade');
        });

        // Add transaction_id to settlement_distributions to track which distributions are already in a transaction
        Schema::table('settlement_distributions', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id')->nullable()->after('file_name');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settlement_distributions', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropColumn('transaction_id');
        });

        Schema::dropIfExists('transaction_bank_allocations');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
    }
};
