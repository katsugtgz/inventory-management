<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * For a document model (Sale, Purchase) that has a paid_amount column and
 * an items() relation whose rows carry a subtotal column.
 */
trait ComputesDocumentTotals
{
    public function recalculateTotals(): void
    {
        $total = (float) $this->items()->sum('subtotal');

        $this->forceFill([
            'total_amount' => $total,
            'due_amount' => max(0, $total - (float) $this->paid_amount),
        ])->save();
    }
}
