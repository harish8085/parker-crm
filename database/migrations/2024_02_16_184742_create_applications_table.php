<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
     
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('app_id')->unique();
            $table->boolean('app_id_is_matched')->nullable();
            $table->string('app_id_is_value')->nullable();
            $table->date('disbursement_date');
            $table->string('case_location');
            $table->boolean('case_location_is_matched')->nullable();
            $table->string('case_location_is_value')->nullable();
            $table->string('case_state');
            $table->string('customer_name');
            $table->boolean('customer_name_is_matched')->nullable();
            $table->string('customer_name_is_value')->nullable();
            $table->string('customer_firm_name')->nullable();
            $table->string('bank_id');
            $table->boolean('bank_id_is_matched')->nullable();
            $table->string('bank_id_is_value')->nullable();
            $table->string('product_id');
            $table->boolean('product_id_is_matched')->nullable();
            $table->string('product_id_is_value')->nullable();
            $table->string('group');
            $table->boolean('group_is_matched')->nullable();
            $table->string('group_is_value')->nullable();
            $table->string('fresh_or_bt')->nullable();
            $table->string('any_subvention')->nullable();
            $table->string('disburse_amount')->nullable();
            $table->boolean('disburse_amount_is_matched')->nullable();
            $table->string('disburse_amount_is_value')->nullable();
            $table->string('commission_rate')->nullable();
            $table->boolean('commission_rate_is_matched')->nullable();
            $table->string('commission_rate_is_value')->nullable();
            $table->string('otc_or_pdd_status')->nullable();
            $table->string('pf_taken')->nullable();
            $table->string('banker_name')->nullable();
            $table->string('banker_number')->nullable();
            $table->string('banker_email')->nullable();
            $table->string('created_by');
            $table->string('remark');
            $table->string('status')->default('pending');
            $table->bigInteger('bank_mis_id')->nullable();
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
        Schema::dropIfExists('applications');
    }
}
