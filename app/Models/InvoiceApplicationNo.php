<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceApplicationNo extends Model
{
    use HasFactory;

    protected $table = 'invoice_application_nos';

    protected $fillable = [
        'invoice_payment_view_id',
        'application_no',
    ];

    public function invoicePayment()
    {
        return $this->belongsTo(InvoicePaymentView::class, 'invoice_payment_view_id');
    }
}
