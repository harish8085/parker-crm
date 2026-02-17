<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTransactionIdToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->unique()->after('id');
        });

        // Backfill existing records
        $transactions = DB::table('transactions')->orderBy('created_at')->orderBy('id')->get();
        $dateCounts = [];
        foreach ($transactions as $txn) {
            $date = date('Ymd', strtotime($txn->created_at));
            if (!isset($dateCounts[$date])) {
                $dateCounts[$date] = 0;
            }
            $dateCounts[$date]++;
            $txnId = 'TXN' . $date . str_pad($dateCounts[$date], 3, '0', STR_PAD_LEFT);
            DB::table('transactions')->where('id', $txn->id)->update(['transaction_id' => $txnId]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });
    }
}
