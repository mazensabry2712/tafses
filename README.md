# Tafses

## Tafses backend
Tafses is a Laravel system for a pomegranate peeling and juice business. The backend is being built first around the real physical and financial workflow: vehicle arrival, separate cold stores (`براد 1`, `براد 2`, etc.), open crate custody, processing, output, waste, supplier purchases, payments, and balances.

## Physical flow
```text
Vehicle / trailer
      |
      +----> براد 1
      |          |
      |          +----> محمود: 21 + 3 + 8 + ...
      |          |          |
      |          |          +--> returned 12
      |          |          +--> outstanding 25
      |
      +----> براد 2
```

Balances are tracked independently at every physical layer:

1. The vehicle keeps its remaining crates/weight.
2. Each cold store keeps its own current crates/weight.
3. Each load inside each cold store is tracked separately.
4. Each custodian has an open custody ledger. Repeated issues stay under the same person and remain open until returned.
5. A return can go back to the vehicle or back to the same cold store.
6. Every issue/return is immutable history, so the system can answer who took what, when, how much was returned, and what is still outstanding.

## Custody example
A person can receive crates multiple times without closing the custody:

```text
Mahmoud
  21
  + 3
  + 8
  + 5
  -----
  37 issued

Returned: 12
Outstanding: 25
```

The outstanding balance is always `issued - returned`. A new issue simply adds to the same open custody balance.

## Stock rules
- Vehicle-to-cold-store cannot move more crates/weight than remains on the vehicle.
- A cold store cannot issue more crates/weight for a specific load than its stock for that load.
- A custodian cannot return more crates/weight than their current outstanding custody for that cold store and load.
- Returning to the vehicle increases the vehicle balance and closes part of the person's open custody.
- Returning to the cold store increases the cold-store balance and closes part of the person's open custody.
- Stock-changing operations use database transactions and row locking.
- Audit transactions are append-only; current balances are retained for fast operational queries.

## Database concepts
- `suppliers`: suppliers / farmers / traders.
- `vehicles`: trucks, trailers, and other transport vehicles.
- `pomegranate_loads`: incoming vehicle loads and current vehicle/facility balances.
- `load_crate_movements`: vehicle/facility movement audit ledger.
- `cold_stores`: separate refrigerators/cold stores such as `براد 1` and `براد 2`.
- `cold_store_stocks`: stock of each incoming load inside each cold store.
- `custodians`: people who take crates from cold stores.
- `custody_transactions`: every issue and return transaction for each custodian.
- `processing_batches`: peeling/juice processing and yield.
- `pomegranate_purchases`: supplier purchase terms and amounts for incoming loads.
- `supplier_payments`: supplier settlement ledger.

## Backend actions
- `CreatePomegranateLoadAction`: creates an incoming load and initializes the vehicle balance.
- `RecordCrateMovementAction`: moves crates/weight between vehicle and facility with locking and validation.
- `MoveLoadToColdStoreAction`: moves crates/weight from a vehicle load into a selected cold store and maintains per-load cold-store stock.
- `IssueCratesToCustodianAction`: issues crates from a cold store to a person and reduces cold-store/load stock.
- `ReturnCustodyAction`: records a person's return and sends crates either to the vehicle or back to the cold store.
- `GetCustodySummaryAction`: returns a cold-store snapshot plus each active person's issued, returned, and outstanding amounts.
- `ProcessPomegranatesAction`: consumes available stock and records peeling/juice output and waste.
- `GetPomegranateLoadSummaryAction`: builds an operational summary for one load.
- `CreatePomegranatePurchaseAction`: creates supplier purchase pricing tied to an incoming load.
- `RecordSupplierPaymentAction`: records a supplier payment while protecting the remaining balance.
- `GetSupplierBalanceAction`: returns purchase total, paid total, and balance due for a supplier.

## Example vehicle flow
A trailer arrives with 100 crates / 2,000 kg.

```text
Vehicle: 100 crates / 2,000 kg
        |
        +--> Move 40 crates / 800 kg to براد 1
        |        |
        |        +--> براد 1: 40 crates / 800 kg
        |                |
        |                +--> Mahmoud takes 21
        |                +--> Mahmoud takes 3
        |                +--> Mahmoud takes 8
        |                +--> Mahmoud returns 12 to vehicle
        |                +--> Mahmoud takes 5 more
        |
        +--> Vehicle remainder becomes 72 crates
```

## Backend-first roadmap
1. Receiving, vehicle/load operations, and base crate movement ✅
2. Separate cold stores and open custodian custody ✅
3. Supplier purchases, prices, payments, and supplier balances ✅
4. Finished-product stock and sales for peeling/juice
5. Daily/monthly reports and operational dashboards
6. Authentication, roles, permissions, and audit access
7. Blade frontend on top of the completed domain layer

Domain actions are the source of truth for stock-changing and settlement operations. UI controllers should validate request shape and delegate business rules to these actions.
