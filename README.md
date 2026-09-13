# Tafses

## Backend domain
Tafses manages pomegranate receiving and processing operations for a facility that sells pomegranate suitable for peeling and juice production.

### Core flow
1. Register the supplier and the vehicle.
2. Create a pomegranate load with the number of crates and total loaded weight.
3. Move crates from the vehicle into the facility. Every movement is stored in an audit ledger.
4. Crates can be returned from the facility to the same vehicle when needed.
5. Process available pomegranate as **peeling** or **juice**.
6. Every processing batch records input crates, input weight, output weight, waste, date, and user.

### Important invariants
- A vehicle cannot unload more crates/weight than currently on the vehicle.
- A vehicle cannot receive more crates/weight than currently available at the facility.
- Processing cannot consume more crates/weight than currently available from the load.
- Processing output + waste cannot exceed processing input weight.
- All stock-changing operations run inside database transactions with row locking.

## Database concepts
- `suppliers`: pomegranate suppliers / farmers / traders.
- `vehicles`: trucks, trailers, and other transport vehicles.
- `pomegranate_loads`: one incoming vehicle load and its current balances.
- `load_crate_movements`: immutable operational audit records for crate/weight movement.
- `processing_batches`: peeling/juice processing records and production yield.

## Next backend phases
The next backend work should add daily reports, supplier balances/purchases, product sales, inventory summaries, permissions, and then the Blade interface. The domain actions should remain the source of truth for stock-changing operations.
