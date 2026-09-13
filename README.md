# Tafses

## Tafses backend
Tafses is a Laravel system for a pomegranate peeling and juice business. The first priority is the real operational flow: vehicle arrival, crate movement, stock inside the facility, processing, output, and waste.

## Operational flow
1. Register the supplier/farmer/trader and transport vehicle.
2. Create an incoming load with a unique load number, crate count, and total weight.
3. The load initially keeps all crates/weight on the vehicle.
4. Move crates from vehicle to facility as they are unloaded. Every move is stored as an audit record.
5. Crates can be returned from the facility to the same vehicle when there is a return or correction.
6. Process available pomegranate as **peeling** or **juice**.
7. Each processing batch records input crates, input weight, output weight, waste, date, user, and notes.
8. A load summary can report loaded quantity, vehicle remainder, available stock, and production by peeling/juice/waste.

## Stock rules
- A vehicle cannot unload more crates or weight than remains on that load's vehicle balance.
- The facility cannot return more crates or weight than the load currently has available.
- Processing cannot consume more crates or weight than the load currently has available.
- Processing output + waste cannot exceed processing input weight.
- Stock-changing operations use database transactions and row locking.
- Operational movement records are append-only audit records; current balances remain on the load for fast operational queries.

## Current database concepts
- `suppliers`: suppliers / farmers / traders.
- `vehicles`: trucks, trailers, and other transport vehicles.
- `pomegranate_loads`: one incoming vehicle load with current on-vehicle and facility balances.
- `load_crate_movements`: movement audit ledger between vehicle and facility.
- `processing_batches`: peeling/juice processing records and yield.

## Current domain actions
- `CreatePomegranateLoadAction`: records a new incoming load and initializes the vehicle balance.
- `RecordCrateMovementAction`: moves crates/weight between the vehicle and facility with locking and validation.
- `ProcessPomegranatesAction`: consumes available stock and records peeling/juice output and waste.
- `GetPomegranateLoadSummaryAction`: builds an operational summary for one load.

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
```

## Backend-first roadmap
1. Receiving and load operations (current).
2. Supplier purchases, prices, payments, and supplier balances.
3. Finished-product stock and sales for peeling/juice.
4. Daily/monthly reports and operational dashboards.
5. Authentication, roles, permissions, and audit access.
6. Blade frontend built on top of the completed domain layer.

The domain actions are the source of truth for stock-changing operations. UI controllers should validate request shape and delegate business rules to these actions.
