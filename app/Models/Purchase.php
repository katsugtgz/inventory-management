<?php

namespace App\Models;

use App\Models\Concerns\ComputesDocumentTotals;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use ComputesDocumentTotals;
    use HasFactory;

    protected $fillable = [
        'supplier_id', 'warehouse_id', 'invoice_no', 'purchase_date',
        'total_amount', 'paid_amount', 'due_amount', 'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
