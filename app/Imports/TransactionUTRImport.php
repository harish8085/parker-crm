<?php

namespace App\Imports;

use App\Models\TransactionBankAllocation;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class TransactionUTRImport implements ToCollection, WithHeadingRow
{
    protected $transactionId;
    protected $updatedCount = 0;
    protected $errors = [];

    public function __construct($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $allocationId = $row['allocation_id'] ?? null;
            $utrNumber = $row['utr_number'] ?? null;

            if (empty($allocationId)) {
                continue;
            }

            if (empty($utrNumber)) {
                continue;
            }

            $allocation = TransactionBankAllocation::where('id', $allocationId)
                ->where('transaction_id', $this->transactionId)
                ->first();

            if (!$allocation) {
                $this->errors[] = "Row " . ($index + 2) . ": Allocation ID {$allocationId} not found for this transaction.";
                continue;
            }

            $allocation->update([
                'utr_number' => trim($utrNumber),
                'payment_date' => now()->toDateString(),
            ]);

            $this->updatedCount++;
        }
    }

    public function getUpdatedCount()
    {
        return $this->updatedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
