<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyNameToApplicationsTable extends Migration
{
    public function up()
    {
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'company_name')) {
                $table->string('company_name')->nullable()->after('customer_firm_name');
            }
            if (!Schema::hasColumn('applications', 'company_name_is_matched')) {
                $table->boolean('company_name_is_matched')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('applications', 'company_name_is_value')) {
                $table->string('company_name_is_value')->nullable()->after('company_name_is_matched');
            }
        });
    }

    public function down()
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'company_name')) {
                $table->dropColumn('company_name');
            }
            if (Schema::hasColumn('applications', 'company_name_is_matched')) {
                $table->dropColumn('company_name_is_matched');
            }
            if (Schema::hasColumn('applications', 'company_name_is_value')) {
                $table->dropColumn('company_name_is_value');
            }
        });
    }
}
