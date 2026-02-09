<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApplicationIdToSettlementDistributionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settlement_distributions', function (Blueprint $table) {
            $table->unsignedBigInteger('application_id')->nullable()->after('user_id');
            $table->decimal('received_rate', 8, 2)->nullable()->after('application_id');
            $table->decimal('gross_amount', 15, 2)->nullable()->after('received_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settlement_distributions', function (Blueprint $table) {
            $table->dropColumn(['application_id', 'received_rate', 'gross_amount']);
        });
    }
}
