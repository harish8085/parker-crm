<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankMisTrackerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_mis_tracker', function (Blueprint $table) {
            $table->id();
            // Correct way: Type first, then column name in quotes
            $table->string('bank_mis_month')->nullable();
            $table->string('bank')->nullable();
            $table->string('product')->nullable();
            $table->string('status')->nullable();
            $table->string('total_cases')->nullable();
            $table->string('matched_cases')->nullable();
            $table->string('unmatched_cases')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_mis_tracker');
    }
}
