<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTdsPercentageToSettlementDistributionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settlement_distributions', function (Blueprint $table) {
            $table->decimal('tds_percentage', 5, 2)->nullable()->after('tds');
        });

        // Backfill existing rows with current TDS value
        \DB::table('settlement_distributions')
            ->whereNull('tds_percentage')
            ->update(['tds_percentage' => 2.0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settlement_distributions', function (Blueprint $table) {
            $table->dropColumn('tds_percentage');
        });
    }
}
