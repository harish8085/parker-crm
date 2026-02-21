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
        Schema::create('contest_mis', function (Blueprint $table) {
            $table->id();
            $table->string('application_no')->index();
            $table->string('location')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->string('customer_name')->nullable();
            $table->decimal('loan_amt', 15, 2)->nullable();
            $table->decimal('contest_rate', 15, 4)->nullable();
            $table->decimal('contest_amt', 15, 2)->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contest_mis');
    }
};

