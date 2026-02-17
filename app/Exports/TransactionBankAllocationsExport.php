<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\TransactionBankAllocation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionBankAllocationsExport implements FromCollection, WithHeadings, WithStyles
{
    protected $transactionId;

    public function __construct($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    public function collection()
    {
        $allocations = TransactionBankAllocation::with('bankAccount')
            ->where('transaction_id', $this->transactionId)
            ->get();

        return $allocations->map(function ($allocation) {
            return [
                'allocation_id' => $allocation->id,
                'holder_name'   => $allocation->bankAccount->holder_name ?? 'N/A',
                'bank_name'     => $allocation->bankAccount->bank_name ?? 'N/A',
                'account_number' => $allocation->bankAccount->account_number ?? 'N/A',
                'ifsc_code'     => $allocation->bankAccount->ifsc_code ?? 'N/A',
                'pan_number'    => $allocation->bankAccount->pan_number ?? '-',
                'aadhar_number' => $allocation->bankAccount->aadhar_number ?? '-',
                'amount'        => $allocation->amount,
                'utr_number'    => $allocation->utr_number ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Allocation ID',
            'Account Holder Name',
            'Bank Name',
            'Account Number',
            'IFSC Code',
            'PAN Number',
            'Aadhar Number',
            'Amount',
            'UTR Number',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
