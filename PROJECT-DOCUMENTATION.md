# Tafses — Project Documentation

## 1. Project identity

Tafses is a Laravel Blade ERP for a pomegranate peeling and juice business.

Architecture direction:
- Laravel + Blade web application.
- Backend/domain logic first, Blade UI on top.
- This project is **not REST/API-first**.
- Domain Actions are the source of truth for stock-changing and settlement operations.
- Controllers validate HTTP input and delegate business rules to Actions.

## 2. Real business workflow

The system models the physical and financial lifecycle of pomegranates:

```text
Supplier / Farmer
      |
      v
Vehicle / Trailer arrival
      |
      v
Pomegranate Load
      |
      +--------------------+
      |                    |
      v                    v
Cold Store 1          Cold Store 2 ...
      |
      +--> Custodian issues
      |       |
      |       +--> repeated issues under same person
      |       +--> partial returns
      |       +--> return to vehicle
      |       +--> return to cold store
      |
      v
Processing
  |        |
  v        v
Peeling   Juice
  |        |
  +----+---+
       v
Finished Product Stock
       |
       v
Sales / Customers / Payments
```

The platform must be able to answer:
- how many crates/weight remain on each vehicle/load;
- how much of each load is in each cold store;
- how many crates each custodian received, returned, and still owes;
- how much raw material was processed;
- how much finished product was produced and sold;
- supplier/customer balances;
- who performed authenticated web actions.

## 3. Core physical rules

### Vehicle and load
A `PomegranateLoad` starts with all loaded crates and weight on the vehicle.

For a load:
- `loaded_*` = original received amount.
- `on_vehicle_*` = current amount still on the vehicle.
- `available_*` = current raw material available for facility/cold-store processing.

Vehicle/facility movements cannot exceed the quantity currently available at their source.

### Cold stores
Cold stores are independent physical locations such as `براد 1`, `براد 2`, etc.

Each cold store has:
- name/code;
- optional crate standard;
- current crate/weight balance;
- active/closed state;
- per-load stock records.

A load can be split across multiple cold stores. Each `ColdStoreStock` records the exact part of that load held by that location.

Closed cold stores cannot receive or issue stock.

A cold store cannot close while:
- current physical stock is greater than zero; or
- custody crates are still outstanding.

### Crate standards
Crate standards define gross and tare weight and derive net weight.

Example:

```text
Gross = 25 kg
Tare  = 2.5 kg
Net   = 22.5 kg

21 crates = 21 x 22.5 = 472.5 kg net
```

When an operation omits explicit weight, the assigned active crate standard may calculate net weight automatically.

## 4. Custody / people taking crates

`Custodians` represent people receiving crates from cold stores.

A custodian can receive repeated issues over time under the same person.

Example:

```text
Mahmoud:
21 + 3 + 8 + 5 = 37 issued
12 returned
25 outstanding
```

The custody summary tracks:
- total issued crates/weight;
- total returned crates/weight;
- current outstanding crates/weight.

A return cannot exceed current outstanding custody.

Return destinations are:
- `vehicle` -> vehicle balance increases;
- `cold_store` -> cold-store stock and facility balance increase.

Custody movements are recorded as transaction history. Returning custody to a vehicle does **not** mean the load is unloaded; the load remains open while vehicle stock exists.

## 5. Processing and finished products

Raw pomegranate can be processed into:
- `peeling` -> Pomegranate Arils;
- `juice` -> Pomegranate Juice;
- recorded waste.

Processing validates:
- process type;
- positive input values;
- output + waste cannot exceed input;
- input crates/weight cannot exceed available raw stock;
- selected cold-store stock cannot be exceeded.

### Stock consistency rule
Processing is tied to the selected active cold store and atomically reduces:

```text
ColdStoreStock
     +
ColdStore.current_*
     +
PomegranateLoad.available_*
```

Each processing batch stores `cold_store_id`, so processing history can be traced to its physical source.

Finished products have current stock plus a transaction ledger.

## 6. Sales and customers

Finished products are sold through invoices.

A sale:
- validates the customer and active product;
- aggregates duplicate product lines before stock validation;
- validates available finished stock;
- creates invoice/items;
- decreases finished stock;
- writes a negative finished-stock transaction;
- records initial payment when provided;
- maintains invoice status.

Invoice statuses:
- `unpaid`
- `partial`
- `paid`

Customer balance is calculated as:

```text
Sales total - Paid total = Balance due
```

Customer payments cannot exceed the remaining balance.

## 7. Supplier purchases and payments

A pomegranate load can have exactly one supplier purchase record.

Supported pricing units:
- `kg`
- `crate`
- `fixed`

A database unique index on `pomegranate_load_id` prevents duplicate purchase rows for a single load. The Action also converts duplicate attempts into a validation error instead of exposing a raw database exception.

Supplier payments are recorded in a ledger and cannot exceed the remaining supplier balance.

## 8. Reports

`GetOperationalReportAction` powers daily, monthly, and custom date-range reporting.

The operational report summarizes:
- receiving;
- supplier purchases and payments;
- processing by type;
- yield/output and waste;
- sales;
- customer balances;
- finished-product stock;
- current active cold-store stock;
- outstanding custody.

## 9. Authentication, roles, and permissions

Native Laravel authentication is used.

Roles:

| Role | Scope |
|---|---|
| `admin` | All permissions |
| `manager` | Operational/finance/report/master-data/audit access, but not user management |
| `storekeeper` | Receiving, cold stores, custody, processing, reports |
| `accountant` | Suppliers, customers, sales, payments, reports |
| `worker` | Processing only |

Permissions:
- `manage_users`
- `receive_loads`
- `manage_cold_stores`
- `manage_custody`
- `process_pomegranates`
- `manage_suppliers`
- `manage_customers`
- `manage_sales`
- `manage_payments`
- `view_reports`
- `view_audit`
- `manage_master_data`

Authorization exists at route middleware level through `permission:` and at application level through `User::canPermission()`.

Inactive users cannot authenticate or use role permissions.

## 10. Management screen

`/management` provides real user management rather than a placeholder.

Current capabilities:
- list users;
- create user;
- assign role during creation;
- activate/deactivate users;
- prevent the current authenticated user from disabling themselves.

Known limitation:
- existing users do not yet have a dedicated inline role-edit form.

## 11. Accounting screen

`/accounting` provides:
- active customer balances;
- supplier balances;
- links to detailed customer/supplier account pages where available.

The route uses the controller `index` method explicitly rather than invokable-controller registration.

## 12. Master Data Management

`/master-data` is the central setup area for records required by operations.

Current management capabilities:
- Vehicles: create vehicles and preserve their historical identity for past loads.
- Cold Stores: create stores and safely close stores through `CloseColdStoreAction`.
- Custodians: create custodians and activate/deactivate them.
- Crate Standards: create standards and activate/deactivate them.
- Finished Products: create products and activate/deactivate them.

Only `admin` and `manager` have `manage_master_data`.

Master data routes:

```text
GET  /master-data
POST /master-data/vehicles
POST /master-data/cold-stores
POST /master-data/cold-stores/{coldStore}/close
POST /master-data/custodians
POST /master-data/custodians/{custodian}/toggle
POST /master-data/crate-standards
POST /master-data/crate-standards/{crateStandard}/toggle
POST /master-data/products
POST /master-data/products/{product}/toggle
```

Mutation redirects intentionally return to the named `master-data` route so HTTP tests and user navigation have a deterministic destination even when no HTTP Referer exists.

## 13. Audit logging

Authenticated web requests create audit records containing request/activity metadata including:
- user;
- role;
- action/event;
- route;
- HTTP method;
- path;
- response status;
- IP address;
- user agent;
- optional context.

The audit screen is protected by `view_audit`.

## 14. Database model map

Main tables:

- `users`
- `suppliers`
- `vehicles`
- `pomegranate_loads`
- `load_crate_movements`
- `pomegranate_purchases`
- `supplier_payments`
- `cold_stores`
- `cold_store_stocks`
- `crate_standards`
- `custodians`
- `custody_transactions`
- `processing_batches`
- `finished_products`
- `finished_product_stocks`
- `finished_product_transactions`
- `customers`
- `finished_product_sales`
- `finished_product_sale_items`
- `customer_payments`
- `audit_logs`

`processing_batches` includes `cold_store_id` to retain the physical source of each processing run.

## 15. Main domain Actions implemented

- `CreatePomegranateLoadAction`
- `RecordCrateMovementAction`
- `MoveLoadToColdStoreAction`
- `IssueCratesToCustodianAction`
- `ReturnCustodyAction`
- `GetCustodySummaryAction`
- `CloseColdStoreAction`
- `ProcessPomegranatesAction`
- `GetPomegranateLoadSummaryAction`
- `GetFinishedProductStockAction`
- `CreateFinishedProductSaleAction`
- `RecordCustomerPaymentAction`
- `GetCustomerBalanceAction`
- `CreatePomegranatePurchaseAction`
- `RecordSupplierPaymentAction`
- `GetSupplierBalanceAction`
- `GetOperationalReportAction`
- `AuthenticateUserAction`

## 16. Controllers / web screens

Current web areas include:
- `/login`
- `/dashboard`
- `/management`
- `/accounting`
- `/master-data`
- `/receiving`
- `/warehouse`
- `/warehouse/custody`
- `/processing`
- `/sales`
- `/suppliers`
- `/reports`
- `/audit`

The Blade UI is RTL Arabic-oriented and currently uses Tailwind CDN for MVP presentation.

## 17. Important implementation guarantees

Stock-changing operations are designed around:
- database transactions;
- `lockForUpdate()` on relevant rows;
- explicit validation before mutation;
- immutable movement/settlement ledger records.

These guarantees are especially important for concurrent operations on:
- vehicle quantities;
- cold-store quantities;
- custodian balances;
- finished-product stock;
- supplier/customer settlements.

Business rules should be fixed in domain logic, not weakened merely to satisfy a test.

## 18. Automated tests

Feature coverage includes:
- authentication;
- role/permission authorization;
- audit logging;
- cold-store custody flows;
- crate-standard calculations;
- receiving and processing HTTP workflow;
- processing and finished stock;
- sales/customer payments;
- supplier purchases/payments;
- reports;
- management/accounting screens;
- master-data management;
- duplicate supplier purchase prevention.

### Current user-confirmed local verification

After pulling commit `c2f96ec`, the user ran:

```text
Tests:    56 passed (276 assertions)
Duration: 2.32s
```

All tests passed on the user's local machine. No new migration was required for the redirect-only commit `c2f96ec`.

## 19. Important production-audit fixes

### Invalid AccountingController route
`AccountingController` exposes `index()`, so `/accounting` is registered explicitly as a controller method route.

### Duplicate supplier purchase
A unique database index plus Action-level validation prevents more than one purchase record per load.

Migration:
`2026_09_13_140001_prevent_duplicate_purchases_per_load.php`

### Processing/cold-store stock divergence
Processing now updates cold-store per-load stock, cold-store aggregate stock, and load available stock atomically and records `cold_store_id` on the batch.

Migration:
`2026_09_13_150001_add_cold_store_to_processing_batches_table.php`

### Custody regression correction
A regression assumption was corrected so returning custody to the vehicle restores vehicle stock while the load remains open.

### HTTP processing-state correction
The processing HTTP test now uses the real sequence: receive -> move to cold store -> process from that cold store.

### Master-data redirect contract
Master-data mutations use deterministic redirects to the `/master-data` named route instead of relying on `back()` with an available HTTP Referer.

## 20. Git / delivery workflow

Work is committed directly to `main` for this project.

Standard local workflow:

```powershell
cd C:\Herd\tafses
git pull origin main
php artisan migrate
php artisan test
```

Do not use `migrate:fresh` against a real data environment just to validate a normal migration.

Always verify that the exact pulled commit is present before treating local tests as evidence for the current repository state.

## 21. Production-readiness checklist

Before deployment, verify:
- production `.env` values;
- `APP_ENV=production`;
- `APP_DEBUG=false`;
- secure `APP_KEY`;
- production database credentials;
- HTTPS;
- regular database backups;
- logs/monitoring;
- final migration backup and deployment plan;
- full test suite against the exact deployment commit.

## 22. Known MVP limitations / planned work

These do not currently block the core physical workflow, but should be addressed in priority order:

### High priority
1. **Stock Adjustments** — formal inventory adjustment for damaged crates, spoilage, physical-count differences, and other approved discrepancies. Every adjustment must have a reason, actor, timestamp, positive/negative movement, immutable ledger entry, and safe balance validation.
2. **Inventory / movement history** — dedicated traceable history for receiving, vehicle movement, cold-store movement, custody issue/return, and processing, with useful filters/search/pagination.
3. **Finished-product inventory/history** — dedicated stock screen and movement history.

### Medium priority
4. Full role editing for existing users.
5. Richer vehicle/load management and history UI.
6. Selectable payment dates instead of always using the current timestamp.
7. Stronger report date validation.
8. Better error UX, empty states, confirmations, search/filter/pagination.

### Production hardening
9. Replace Tailwind CDN with compiled production assets when the UI is finalized.
10. Production-safe data migration review where the existing database is already populated.
11. Concurrency-focused tests.
12. Backup/restore drill, deployment procedure, monitoring, and final security review.
13. Full end-to-end business journey test from receiving through sale/payment.

## 23. Current status

Core MVP domains implemented:

```text
Receiving / Loads             ✅
Vehicle movement              ✅
Separate cold stores          ✅
Crate standards               ✅
Open custody ledger           ✅
Processing                    ✅
Finished stock                ✅
Sales / customers             ✅
Supplier purchases/payments   ✅
Operational reports           ✅
Authentication                ✅
Roles / permissions            ✅
Audit log                     ✅
Management screen             ✅
Accounting screen             ✅
Master Data                   ✅
Blade screens                 ✅
Automated feature tests       ✅

Stock Adjustments             ⏳
Movement history              ⏳
Finished inventory screen     ⏳
Production hardening          🔄
```

### Verified local baseline

```text
56 passed (276 assertions)
```

The working principle remains: build a traceable business platform first. Non-blocking cosmetic enhancements must not replace fixing real business-data integrity risks.

## 24. Working rule for future changes

Do not weaken domain rules just to make a test pass.

For every business-data change:
1. identify the real physical or financial invariant;
2. change the Domain Action first;
3. keep controller logic thin and HTTP-focused;
4. wrap stock-changing mutations in database transactions;
5. lock relevant rows with `lockForUpdate()` where concurrent balance changes are possible;
6. record immutable ledger/history entries for physical or financial movements;
7. add/update Feature tests for the real business scenario;
8. run the full local test suite;
9. commit the verified change to `main`;
10. pull the exact commit locally before accepting the next change.
