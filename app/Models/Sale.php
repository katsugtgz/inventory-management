<?php

namespace App\Models;

use App\Models\Concerns\ComputesDocumentTotals;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use ComputesDocumentTotals;
    use HasFactory;

    protected $fillable = [
        'customer_id', 'warehouse_id', 'invoice_no', 'sale_date',
        'total_amount', 'paid_amount', 'due_amount', 'status',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
