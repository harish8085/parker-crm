<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePaymentView extends Model
{
    use HasFactory;

    protected $table = 'invoice_payment_view';

    protected $fillable = [
        'bank_name',
        'bank_address',
        'invoice_no',
        'mis_month',
        'group_name',
        'invoice_date',
        'bank_gst_no',
        'bank_hsn_code',
        'dsa_pan',
        'dsa_gst_no',
        'application_no',
        'payment_amount',
        'CGST',
        'SGST',
        'IGST',
        'TDS',
        'invoice_value',
        'taxable_value',
        'payment_received_bank',
        'remaining_amount',
        'payment_status',
        'payment_paid',
        'company_name',
        'payment_date1',
        'payment_date2',
       
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Check if an application ID has an invoice
     */
    public static function hasInvoice($appId)
    {
        return self::get()
            ->filter(function($invoice) use ($appId) {
                $appIds = explode(',', $invoice->application_no);
                return in_array(trim($appId), array_map('trim', $appIds));
            })
            ->isNotEmpty();
    }

    /**
     * Get all invoiced application IDs
     */
    public static function getInvoicedAppIds()
    {
        return self::get()
            ->map(function($invoice) {
                return explode(',', $invoice->application_no);
            })
            ->flatten()
            ->map(function($appId) {
                return trim($appId);
            })
            ->unique()
            ->toArray();
    }
}

