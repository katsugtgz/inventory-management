# Glossary

Domain vocabulary for the inventory system. Use these terms in code, tests, and discussion.

## Stock ledger

The module (`App\Support\StockLedger`) that owns every change to the `stocks` table. One interface: `StockLedger::apply(product_id, warehouse_id, delta)`. Purchases, sales, and stock adjustments are *sources* that compute a signed delta and hand it to the ledger; they never write `stocks` rows directly. The `stocks` table is derived state — a running total, never hand-edited.

## Report query module

One module per report (`App\Reports\`): `base()` builds the query, `applyFilters()` applies the named filter signature. Report pages, Excel exports, and PDF exports are all *adapters* over the same query module — a report's numbers are defined once, never re-implemented per output format.

## Document totals

`Sale` and `Purchase` derive `total_amount` from the sum of their items' `subtotal`, and `due_amount` from `total_amount − paid_amount` (floored at 0), via `ComputesDocumentTotals::recalculateTotals()`. Money fields on the forms are display-only; the server owns the math.
