# Tafses

## Tafses backend
Tafses is a Laravel system for a pomegranate peeling and juice business. The backend is being built first around the real physical and financial workflow: vehicle arrival, separate cold stores (`براد 1`, `براد 2`, etc.), open crate custody, processing, finished-product stock, sales, customer payments, supplier purchases, balances, and operational reporting.

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

## Crate weight standard
Crate standards are stored centrally and can be assigned to each cold store. Example:

```text
Gross crate weight = 25 kg
Empty crate / tare = 2.5 kg
Net pomegranate   = 22.5 kg

21 crates => 21 x 22.5 = 472.5 kg net
```

When a cold-store operation does not receive an explicit weight, the assigned crate standard is used to calculate the net weight automatically.

## Stock rules
- Vehicle-to-cold-store cannot move more crates/weight than remains on the vehicle.
- A cold store cannot issue more crates/weight for a specific load than its stock for that load.
- A custodian cannot return more crates/weight than their current outstanding custody for that cold store and load.
- Returning to the vehicle increases the vehicle balance and closes part of the person's open custody.
- Returning to the cold store increases the cold-store balance and closes part of the person's open custody.
- A cold store cannot be closed while it still has stock or custody crates outstanding.
- Closed cold stores cannot receive new stock or issue new custody.
- Stock-changing operations use database transactions and row locking.
- Audit transactions are append-only; current balances are retained for fast operational queries.

## Processing and finished products
Raw pomegranate available for processing can be processed into either:

- `peeling` -> Pomegranate Arils
- `juice` -> Pomegranate Juice
- plus recorded waste

Each finished product has its own current stock and transaction ledger. Production increases finished stock; sales decrease finished stock.

## Sales and customer balances
Finished products can be sold through invoices:

```text
Finished product stock
        |
        +--> Sale invoice
        |      |
        |      +--> sale items / quantity / unit price
        |      +--> stock decreases
        |      +--> customer debt increases
        |
        +--> customer payment
               |
               +--> invoice paid amount increases
               +--> customer balance decreases
```

Rules:
- A sale must contain at least one finished-product item.
- A sale cannot exceed the available finished-product stock.
- Repeating the same product in one invoice is aggregated for stock validation so it cannot oversell the stock.
- Initial invoice payment is recorded in the customer payment ledger.
- Later payments may be linked to a specific invoice and cannot exceed that invoice's remaining balance.
- Invoice status is `unpaid`, `partial`, or `paid`.
- Customer balance is `sales total - paid total`.

## Operational reports
`GetOperationalReportAction` is a reusable reporting layer that accepts a `from` and `to` date/time and can therefore power daily, monthly, or custom-period reports without duplicating business logic.

The report currently summarizes:

- receiving: incoming loads, crates, and weight;
- purchases: purchase count, total, paid, and remaining supplier balance for the period;
- supplier payments: count and total paid;
- processing: batches, input crates/weight, output, waste, plus peeling/juice breakdown;
- sales: invoice count, total sales, paid amount, and customer balance generated in the period;
- current finished-product stock;
- current active cold-store stock and outstanding custody crates.

This keeps operational reporting separate from stock-changing actions while using the same persisted balances and ledgers as the source of truth.

## Database concepts
- `suppliers`: suppliers / farmers / traders.
- `vehicles`: trucks, trailers, and other transport vehicles.
- `pomegranate_loads`: incoming vehicle loads and current vehicle/facility balances.
- `load_crate_movements`: vehicle/facility movement audit ledger.
- `cold_stores`: separate refrigerators/cold stores such as `براد 1` and `براد 2`.
- `cold_store_stocks`: stock of each incoming load inside each cold store.
- `crate_standards`: gross/tare/net crate weight rules.
- `custodians`: people who take crates from cold stores.
- `custody_transactions`: every issue and return transaction for each custodian.
- `processing_batches`: peeling/juice processing and yield.
- `finished_products`: finished products such as peeling and juice.
- `finished_product_stocks`: current finished-product balances.
- `finished_product_transactions`: production and stock-out ledger for finished products.
- `customers`: buyers/customers.
- `finished_product_sales`: sales invoices and customer debt status.
- `finished_product_sale_items`: products, quantities, and unit prices per invoice.
- `customer_payments`: customer settlement ledger.
- `pomegranate_purchases`: supplier purchase terms and amounts for incoming loads.
- `supplier_payments`: supplier settlement ledger.

## Backend actions
- `CreatePomegranateLoadAction`: creates an incoming load and initializes the vehicle balance.
- `RecordCrateMovementAction`: moves crates/weight between vehicle and facility with locking and validation.
- `MoveLoadToColdStoreAction`: moves crates/weight from a vehicle load into a selected cold store and maintains per-load cold-store stock.
- `IssueCratesToCustodianAction`: issues crates from a cold store to a person and reduces cold-store/load stock.
- `ReturnCustodyAction`: records a person's return and sends crates either to the vehicle or back to the cold store.
- `GetCustodySummaryAction`: returns a cold-store snapshot plus each active person's issued, returned, and outstanding amounts.
- `CloseColdStoreAction`: closes a cold store only after physical stock and custody are settled.
- `ProcessPomegranatesAction`: consumes available stock and records peeling/juice output and waste.
- `GetFinishedProductStockAction`: returns current stock for a finished product.
- `CreateFinishedProductSaleAction`: creates an invoice, validates stock, reduces finished-product stock, and records the sale ledger movement.
- `RecordCustomerPaymentAction`: records a customer payment and updates the linked invoice status/balance.
- `GetCustomerBalanceAction`: returns customer sales total, paid total, and balance due.
- `GetOperationalReportAction`: builds daily/monthly/custom-period operational summaries.
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
2. Separate cold stores, crate standards, and open custodian custody ✅
3. Supplier purchases, prices, payments, and supplier balances ✅
4. Finished-product stock for peeling/juice ✅
5. Finished-product sales, customers, and customer payments ✅
6. Daily/monthly operational reports ✅
7. Authentication, roles, permissions, and audit access
8. Blade frontend on top of the completed domain layer

Domain actions are the source of truth for stock-changing and settlement operations. UI controllers should validate request shape and delegate business rules to these actions.
