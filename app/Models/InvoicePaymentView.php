<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceApplicationNo;

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
        'payment_paid1',
        'payment_paid2',
        'company_name',
        'payment_date1',
        'payment_date2',
        'referance_no1',
        'referance_no2',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function applicationNos()
    {
        return $this->hasMany(InvoiceApplicationNo::class, 'invoice_payment_view_id');
    }

    /**
     * Check if an application ID has an invoice
     */
    public static function hasInvoice($appId)
    {
        return InvoiceApplicationNo::where('application_no', trim($appId))->exists();
    }

    /**
     * Get all invoiced application IDs
     */
    public static function getInvoicedAppIds()
    {
        return InvoiceApplicationNo::query()
            ->pluck('application_no')
            ->map(function ($appId) {
                return trim($appId);
            })
            ->unique()
            ->toArray();
    }
}

