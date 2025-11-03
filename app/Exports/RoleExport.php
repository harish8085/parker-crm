<?php

namespace App\Exports;

use App\Models\Role;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RoleExport implements FromCollection, WithHeadings
{
    protected $counter = 0;

    public function collection()
    {
      return Role::whereNotIn('id',[1,2,3])->get()->map(function ($role) {
            $this->counter++;
            return [
                'S No.' => $this->counter,
                'Date Time' =>Carbon::parse($role->created_at)->format('d-m-Y'), // Format the current date and time
                'Role Name' => $role->name,
                'Status' => ($role->status)?'Active':'In-Active',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'S No.',
            'Date Time',
            'name',
            'status',
        ];
    }

    
}
