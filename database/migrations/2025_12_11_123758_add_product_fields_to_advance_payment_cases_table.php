<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductFieldsToAdvancePaymentCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advance_payment_cases', function (Blueprint $table) {
            $table->string('product')->nullable()->after('application_id');
            $table->decimal('product_percent', 8, 2)->nullable()->after('product');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advance_payment_cases', function (Blueprint $table) {
            $table->dropColumn(['product', 'product_percent']);
        });
    }
}
