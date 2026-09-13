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

Every important physical movement is tracked so the system can answer:
- how many crates are on the vehicle;
- how many crates are in each cold store;
- how much of each load is inside each cold store;
- how many crates each custodian received and returned;
- what each custodian still owes;
- how much raw material was processed;
- how much finished product was produced;
- what was sold and what remains in finished stock;
- supplier and customer balances;
- who performed authenticated web actions.

## 3. Core physical rules

### Vehicle and load
A `PomegranateLoad` starts with all loaded crates and weight on the vehicle.

For a load:
- `loaded_*` = original received amount.
- `on_vehicle_*` = current amount still on the vehicle.
- `available_*` = current raw material available in facility/cold-store processing stock.

Vehicle-to-facility movement cannot exceed what remains on the vehicle.

### Cold stores
Cold stores are independent physical locations such as:
- `براد 1`
- `براد 2`
- `براد 3`

Each cold store has:
- name/code;
- optional crate standard;
- current crates count;
- current weight;
- active/closed state;
- per-load stock records.

A load can therefore exist in multiple cold stores, while each cold store still knows exactly how much of that load it owns.

Closed cold stores cannot receive or issue stock.

A cold store cannot be closed while:
- current physical stock is greater than zero; or
- custody crates are still outstanding.

### Crate standard
Crate standards define gross and tare weight and derive net weight.

Example:

```text
Gross = 25 kg
Tare  = 2.5 kg
Net   = 22.5 kg

21 crates = 21 x 22.5 = 472.5 kg net
```

For cold-store issue/move/return operations, when explicit weight is not supplied, the cold store's assigned crate standard calculates the net weight automatically.

## 4. Custody / people taking crates

`Custodians` represent people receiving crates from cold stores.

A custodian can receive multiple open issues over time under the same person.

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

A return cannot exceed the custodian's currently outstanding amount.

A return destination is one of:
- `vehicle` -> vehicle balance increases;
- `cold_store` -> cold-store stock and facility balance increase.

All issue/return operations are recorded as immutable transaction history.

## 5. Processing and finished products

Raw pomegranate can be processed into:
- `peeling` -> Pomegranate Arils;
- `juice` -> Pomegranate Juice;
- plus recorded waste.

Processing validates:
- process type;
- positive input values;
- output + waste cannot exceed input;
- input crates/weight cannot exceed available raw stock;
- selected cold-store stock cannot be exceeded.

### Important stock-consistency rule
Processing is tied to the selected cold store and updates all related balances atomically:

```text
ColdStoreStock
     +
ColdStore.current_*
     +
PomegranateLoad.available_*
```

The three physical aggregates are kept consistent in the same database transaction.

Each processing batch stores its `cold_store_id` so production history is traceable back to the physical source.

Finished product stock has its own current balance and transaction ledger.

## 6. Sales and customers

Finished products are sold through invoices.

A sale:
- validates the customer and active product;
- validates available finished stock;
- aggregates duplicate product lines before stock validation;
- creates invoice/items;
- decreases finished stock;
- records a negative finished-product stock ledger transaction;
- records initial payment when present;
- maintains invoice status.

Invoice statuses:
- `unpaid`
- `partial`
- `paid`

Customer balance:

```text
Sales total - Paid total = Balance due
```

Later customer payments cannot exceed the remaining invoice balance.

## 7. Supplier purchases and payments

A pomegranate load can have one supplier purchase record.

Supported pricing units:
- `kg`
- `crate`
- `fixed`

Quantity can default from the load when the pricing model supports it.

Initial supplier payment is written into the supplier payment ledger.

A later supplier payment cannot exceed the remaining purchase balance.

A database unique index prevents more than one purchase record for the same `pomegranate_load_id`.

## 8. Reports

`GetOperationalReportAction` powers daily, monthly, and custom date-range reporting.

The operational report summarizes:
- receiving;
- supplier purchases;
- supplier payments;
- processing by type;
- yield/output;
- waste;
- sales;
- customer balances;
- finished-product stock;
- current active cold-store stock;
- outstanding custody crates.

The report logic is centralized in an Action instead of duplicated across controllers/views.

## 9. Authentication, roles, and permissions

Native Laravel authentication is used.

Roles currently defined:

| Role | Scope |
|---|---|
| `admin` | All permissions |
| `manager` | Operational/finance/report/audit access, but not user management |
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

Authorization exists at route middleware level through `permission:` and at application level through `User::canPermission()`.

Inactive users cannot authenticate or use role permissions.

## 10. Management screen

The management area is implemented rather than left as a placeholder.

Current capabilities:
- list users;
- create user;
- assign role during creation;
- activate/deactivate users;
- prevent the currently authenticated admin from disabling themselves.

Current limitation intentionally left for later:
- existing users do not yet have an inline role-edit form.

## 11. Accounting screen

The accounting area is implemented rather than left as a placeholder.

It exposes:
- active customer balances;
- supplier balances;
- links to detailed customer/supplier account pages where available.

## 12. Audit logging

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

## 13. Database model map

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

## 14. Main domain Actions implemented

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

## 15. Controllers / web screens

Current web areas include:
- `/login`
- `/dashboard`
- `/management`
- `/accounting`
- `/receiving`
- `/warehouse`
- `/warehouse/custody`
- `/processing`
- `/sales`
- `/suppliers`
- `/reports`
- `/audit`

The Blade UI is RTL Arabic-oriented and currently uses Tailwind CDN for the MVP presentation layer.

## 16. Important implementation guarantees

Stock-changing operations are designed around:
- database transactions;
- `lockForUpdate()` on relevant rows;
- explicit validation before mutation;
- immutable movement/settlement ledger records.

This is especially important for concurrent operations on:
- vehicle quantities;
- cold-store quantities;
- custodian balances;
- finished-product stock;
- supplier/customer settlements.

## 17. Automated tests

The repository currently contains Feature tests covering:
- authentication;
- role/permission authorization;
- audit logging;
- cold-store custody flows;
- crate-standard weight calculation;
- receiving and processing HTTP workflow;
- processing and finished stock;
- sales/customer payments;
- supplier purchases/payments;
- reporting;
- management/accounting screens;
- duplicate supplier purchase prevention.

Latest user-confirmed local baseline before the newest processing consistency update:

```text
52 passed (236 assertions)
```

The current `main` branch then received the subsequent processing consistency/audit updates. Those newest changes must be pulled and tested locally before treating the new code as locally verified.

## 18. Recent production-audit findings and fixes

### Issue A — invalid AccountingController route
Problem:
`AccountingController` was registered as an invokable controller even though it exposes `index()`.

Fix:

```php
Route::get('/accounting', [AccountingController::class, 'index']);
```

Result:
Migration and tests could load the route table normally.

### Issue B — duplicate supplier purchase per load
Problem:
The application could attempt multiple purchase rows for one incoming load.

Fixes:
- database unique index on `pomegranate_load_id`;
- Action-level validation using `ValidationException` instead of exposing a raw DB error.

### Issue C — processing/cold-store stock divergence
Problem:
Processing originally reduced `PomegranateLoad.available_*` but did not reduce the corresponding cold-store stock aggregates.

Fix:
Processing now accepts the selected cold store and atomically reduces:
- the per-load `ColdStoreStock` balance;
- the cold store current balance;
- the load available balance;
- while recording `cold_store_id` on the processing batch.

Regression tests were added for the domain and HTTP processing flows.

### Issue D — incorrect custody regression test
A temporary regression test assumed returning custody to the vehicle should mark the load `unloaded`. That assumption was incorrect because the stock had returned to the vehicle.

The test was corrected to assert that the vehicle balance is restored and the load remains `open`.

## 19. Git / delivery workflow

Work is committed directly to `main` for this project.

Standard local workflow:

```powershell
cd C:\Herd\tafses
git pull origin main
php artisan migrate
php artisan test
```

Do not use `migrate:fresh` against a real data environment just to validate a normal migration.

## 20. Production-readiness checklist

Before deployment, verify:

### Required
- production `.env` values;
- `APP_ENV=production`;
- `APP_DEBUG=false`;
- secure `APP_KEY`;
- production database credentials;
- HTTPS;
- queue/scheduler configuration if later introduced;
- regular database backups;
- log/monitoring strategy;
- final migration backup and deployment plan;
- local test suite passes after pulling the exact deployment commit.

### Known MVP limitations / future enhancements
These are not currently considered blockers to the core physical workflow, but should be planned:
- full role editing for existing users;
- richer vehicle/load history UI;
- dedicated finished-product inventory/history screen;
- more detailed custody weight analytics in operational reports;
- selectable payment dates instead of always using the current timestamp;
- explicit date-format validation for report query parameters;
- replacing Tailwind CDN with a compiled production asset pipeline when the UI is finalized;
- production-safe data migration review if duplicate purchase records already exist in a populated database.

## 21. Current status

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
Supplier purchases/payments  ✅
Operational reports           ✅
Authentication               ✅
Roles / permissions           ✅
Audit log                    ✅
Management screen             ✅
Accounting screen             ✅
Blade screens                 ✅
Automated feature tests       ✅

Final production hardening    🔄
```

The target is a working, traceable business platform first; cosmetic enhancements and non-blocking convenience features should not replace fixing real business-data integrity issues.

## 22. Last audited commits

Relevant recent commits on `main`:

- `666f72c` — fix AccountingController route registration.
- `11513dc` — added custody regression coverage, later corrected.
- `5842353` — corrected custody regression test scenario.
- `80e1c111` — processing/cold-store stock consistency audit update and related tests.
- Current documentation commit: this file.
