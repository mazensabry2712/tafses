# Tafses

## Tafses backend
Tafses is a Laravel system for a pomegranate peeling and juice business. The backend is being built first around the real operational and financial workflow: vehicle arrival, crate movement, stock inside the facility, processing, output, waste, supplier purchases, payments, and balances.

## Operational flow
1. Register the supplier/farmer/trader and transport vehicle.
2. Create an incoming load with a unique load number, crate count, and total weight.
3. The load initially keeps all crates/weight on the vehicle.
4. Move crates from vehicle to facility as they are unloaded. Every move is stored as an audit record.
5. Crates can be returned from the facility to the same vehicle when there is a return or correction.
6. Process available pomegranate as **peeling** or **juice**.
7. Each processing batch records input crates, input weight, output weight, waste, date, user, and notes.
8. A load summary reports loaded quantity, vehicle remainder, available stock, and production by peeling/juice/waste.
9. Create one purchase record for each incoming load when the supplier price is known.
10. Purchase pricing can be **per kg**, **per crate**, or a **fixed total amount**.
11. Supplier payments are recorded separately and can never exceed the remaining purchase balance.
12. Supplier balances are calculated from purchase totals and recorded payments.

## Stock rules
- A vehicle cannot unload more crates or weight than remains on that load's vehicle balance.
- The facility cannot return more crates or weight than the load currently has available.
- Processing cannot consume more crates or weight than the load currently has available.
- Processing output + waste cannot exceed processing input weight.
- Stock-changing operations use database transactions and row locking.
- Operational movement records are append-only audit records; current balances remain on the load for fast operational queries.

## Financial rules
- A purchase belongs to exactly one incoming pomegranate load.
- The supplier is derived from the load to keep the purchase tied to the actual receiving event.
- `kg` pricing defaults to the loaded weight.
- `crate` pricing defaults to the loaded crate count.
- `fixed` pricing uses quantity `1` and the supplied unit price as the total.
- Initial payment may be recorded on purchase creation.
- Later payments are separate records linked to the purchase and supplier.
- A payment cannot exceed the purchase's remaining balance.
- Supplier balance is never allowed to become negative.

## Current database concepts
- `suppliers`: suppliers / farmers / traders.
- `vehicles`: trucks, trailers, and other transport vehicles.
- `pomegranate_loads`: one incoming vehicle load with current on-vehicle and facility balances.
- `load_crate_movements`: movement audit ledger between vehicle and facility.
- `processing_batches`: peeling/juice processing records and yield.
- `pomegranate_purchases`: supplier purchase terms and amounts for incoming loads.
- `supplier_payments`: payment ledger for supplier settlements.

## Current domain actions
- `CreatePomegranateLoadAction`: records a new incoming load and initializes the vehicle balance.
- `RecordCrateMovementAction`: moves crates/weight between the vehicle and facility with locking and validation.
- `ProcessPomegranatesAction`: consumes available stock and records peeling/juice output and waste.
- `GetPomegranateLoadSummaryAction`: builds an operational summary for one load.
- `CreatePomegranatePurchaseAction`: creates supplier purchase pricing tied to an incoming load.
- `RecordSupplierPaymentAction`: records a supplier payment while protecting the remaining balance.
- `GetSupplierBalanceAction`: returns purchase total, paid total, and balance due for a supplier.

## Example business flow
A trailer arrives with 420 crates and 8,400 kg.

```text
Incoming load
420 crates / 8,400 kg
        |
        +--> Unload 300 crates / 6,000 kg
        |        |
        |        +--> Facility available: 300 crates / 6,000 kg
        |
        +--> Vehicle remaining: 120 crates / 2,400 kg

Facility processing
300 crates / 6,000 kg
        |
        +--> Peeling output
        +--> Juice output
        +--> Waste

Supplier settlement
Load purchase total
        |
        +--> Initial payment
        +--> Later payments
        +--> Remaining supplier balance
```

## Backend-first roadmap
1. Receiving, vehicle/load operations, and crate movement (complete foundation).
2. Supplier purchases, prices, payments, and supplier balances (current).
3. Finished-product stock and sales for peeling/juice.
4. Daily/monthly reports and operational dashboards.
5. Authentication, roles, permissions, and audit access.
6. Blade frontend built on top of the completed domain layer.

The domain actions are the source of truth for stock-changing and settlement operations. UI controllers should validate request shape and delegate business rules to these actions.
