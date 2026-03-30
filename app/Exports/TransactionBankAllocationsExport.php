<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\TransactionBankAllocation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TransactionBankAllocationsExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles
{
    protected $transactionId;

    public function __construct($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    public function collection()
    {
        return TransactionBankAllocation::with('bankAccount')
            ->where('transaction_id', $this->transactionId)
            ->get();
    }

    public function map($allocation): array
    {
        return [
            (int) $allocation->id,
            $allocation->bankAccount->holder_name ?? 'N/A',
            $allocation->bankAccount->bank_name ?? 'N/A',
            $allocation->bankAccount->account_number ?? 'N/A',
            $allocation->bankAccount->ifsc_code ?? 'N/A',
            $allocation->bankAccount->pan_number ?? '-',
            $allocation->bankAccount->aadhar_number ?? '-',
            $allocation->amount !== null ? (float) $allocation->amount : null,
            $allocation->utr_number ?? '',
            $allocation->payment_date ? Date::dateTimeToExcel($allocation->payment_date) : null,
        ];
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
            'Payment Date',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_NUMBER_00,
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
