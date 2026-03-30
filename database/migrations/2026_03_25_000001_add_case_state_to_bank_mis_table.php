<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaseStateToBankMisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_mis', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_mis', 'case_state')) {
                $table->string('case_state')->nullable()->after('case_location');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_mis', function (Blueprint $table) {
            if (Schema::hasColumn('bank_mis', 'case_state')) {
                $table->dropColumn('case_state');
            }
        });
    }
}

