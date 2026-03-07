<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateInvoiceApplicationNosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_application_nos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_payment_view_id');
            $table->string('application_no');
            $table->timestamps();

            $table->index(['invoice_payment_view_id']);
            $table->index(['application_no']);
        });

        // Backfill existing comma-separated application numbers.
        $invoices = DB::table('invoice_payment_view')->select('id', 'application_no')->get();
        foreach ($invoices as $invoice) {
            if (!$invoice->application_no) {
                continue;
            }
            $appNos = array_filter(array_map('trim', explode(',', $invoice->application_no)));
            foreach ($appNos as $appNo) {
                DB::table('invoice_application_nos')->insert([
                    'invoice_payment_view_id' => $invoice->id,
                    'application_no' => $appNo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasColumn('invoice_payment_view', 'application_no')) {
            DB::statement('ALTER TABLE invoice_payment_view DROP COLUMN application_no');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_payment_view', function (Blueprint $table) {
            $table->string('application_no')->nullable();
        });

        // Best-effort restore comma-separated values.
        $invoiceIds = DB::table('invoice_application_nos')->select('invoice_payment_view_id')->distinct()->pluck('invoice_payment_view_id');
        foreach ($invoiceIds as $invoiceId) {
            $appNos = DB::table('invoice_application_nos')
                ->where('invoice_payment_view_id', $invoiceId)
                ->orderBy('id')
                ->pluck('application_no')
                ->toArray();

            DB::table('invoice_payment_view')
                ->where('id', $invoiceId)
                ->update([
                    'application_no' => implode(',', $appNos),
                ]);
        }

        Schema::dropIfExists('invoice_application_nos');
    }
}
