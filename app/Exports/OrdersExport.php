<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersExport implements FromCollection, WithHeadings
{
    /**
    * @return Collection
    */
    public function collection(): Collection
    {
        return Order::select('id', 'product_name', 'amount', 'status', 'created_at')->where('user_id', auth()->id())->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Product Name',
            'Quantity',
            'Status',
            'Created At',
        ];
    }
}
