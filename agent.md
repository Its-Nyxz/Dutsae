# AGENT.md — POS Toko Besi

## 1. Project Identity

**Project Name:** POS Toko Besi  
**Type:** Web-based Point of Sale (POS) and Inventory Management System  
**Primary Language:** Indonesian (`id-ID`)  
**Currency:** Indonesian Rupiah (`IDR`)  
**Timezone:** `Asia/Jakarta`

This project is a web-based POS system specifically designed for a hardware / iron / building-material store.

The system must support a workflow where master data can grow naturally while the store is operating. A cashier must be able to create a previously unknown product directly from the sales screen without leaving the current transaction.

The most important domain concerns are:

- product master data;
- manual product codes;
- product locations;
- flexible units and per-product unit conversions;
- stock calculation;
- stock limits / minimum stock;
- sales;
- payments / cash inflow;
- daily turnover / omzet;
- dashboard summaries;
- stock history / auditability.

---

# 2. Technology Stack

Use the following stack unless explicitly instructed otherwise.

## Backend

- PHP 8.3+
- Laravel 13
- MySQL 8+
- Laravel migrations, Eloquent, validation, policies, transactions, queues only when needed

## Frontend

- Livewire 4
- Tailwind CSS 4
- Alpine.js bundled through Livewire
- Blade

## Development Principles

Prefer Laravel-native solutions.

Do NOT introduce Vue, React, Inertia, Filament, Redis, Elasticsearch, external state managers, or large JavaScript libraries unless explicitly requested or there is a documented technical need.

The primary POS screen must be custom-built using Livewire + Alpine, not generated admin CRUD UI.

---

# 3. Core Architectural Principles

## 3.1 Business Logic Must Not Live Entirely in Livewire Components

Livewire components should orchestrate user interaction.

Complex domain logic must be placed in dedicated services/actions, for example:

```text
app/
├── Actions/
├── Services/
│   ├── CheckoutService.php
│   ├── InventoryService.php
│   ├── ProductService.php
│   ├── PricingService.php
│   └── PaymentService.php
```

Avoid giant Livewire components containing hundreds of lines of stock, payment, pricing, and checkout logic.

---

## 3.2 Use Database Transactions for Critical Operations

Any operation that affects sales, payments, and stock together must use:

```php
DB::transaction(...)
```

Checkout must be atomic.

A completed sale must never exist without its corresponding sale items and stock movements.

Likewise, stock must not be reduced if the sale fails.

---

## 3.3 Stock Concurrency Must Be Safe

When updating available inventory during checkout, lock the affected inventory row:

```php
->lockForUpdate()
```

This prevents two cashiers from selling the same final stock simultaneously.

---

# 4. Product Rules

## 4.1 Product Code Is MANUAL

Product codes must be entered manually by the user.

Never auto-generate a product code unless a future requirement explicitly changes this decision.

Examples:

```text
B10
B12
HS44
PLAT2
PIPA34
```

Rules:

- product code is required;
- trim leading/trailing whitespace;
- product code must be unique within one store;
- product code should not automatically change when the product name changes;
- search must support product code and product name;
- product code is not a barcode unless specifically assigned as one.

Recommended unique key:

```text
UNIQUE(store_id, code)
```

---

# 5. Product Creation During POS Transactions

A key feature of this application is **Quick Create Product**.

If the cashier searches for a product that does not yet exist, the cashier must be able to create it from inside the POS screen.

Example flow:

```text
Search:
B10

No product found.

[ + Buat Barang Baru ]
```

The entered search value may prefill the product code field, but the value must remain editable and must still be considered manually entered.

Minimum quick-create fields:

```text
Kode Barang       required
Nama Barang       required
Lokasi Barang     optional/required based on settings
Satuan Dasar      required
Harga Jual        required
Stok Awal         optional
Minimum Stok      optional
```

After saving:

```text
Create Product
    ↓
Create Product Unit
    ↓
Create Opening Stock Movement if initial stock > 0
    ↓
Update Inventory Balance
    ↓
Add Product To Current Sale
```

The cashier must not be forced to navigate to a separate master-product page.

---

# 6. Units of Measure

Units must use a separate master table.

## 6.1 `units`

Example:

```text
units
-------------------------
id
code
name
symbol
allow_decimal
created_at
updated_at
```

Sample data:

| code | name | symbol | allow_decimal |
|---|---|---|---|
| PCS | Pieces | pcs | false |
| M | Meter | m | true |
| BTG | Batang | btg | false |
| KG | Kilogram | kg | true |
| LBR | Lembar | lbr | false |
| IKAT | Ikat | ikat | false |
| ROLL | Roll | roll | false |

Unit names must come from this master table rather than free-text values on products.

If a needed unit does not exist, support creating a unit from the relevant workflow when appropriate.

---

# 7. Product Units and Conversion

Conversions are NOT global.

A unit only defines the type of unit.

The conversion factor belongs to the product-unit relationship.

Use a table such as:

```text
product_units
-------------------------
id
product_id
unit_id
conversion_factor
selling_price
barcode
is_base_unit
created_at
updated_at
```

Recommended constraint:

```text
UNIQUE(product_id, unit_id)
```

A product must have exactly one base unit.

For the base unit:

```text
conversion_factor = 1
is_base_unit = true
```

Example:

Product:

```text
B10 — Besi Beton 10mm
```

Product units:

| unit | conversion_factor | base | selling_price |
|---|---:|---|---:|
| Meter | 1 | yes | 15000 |
| Batang | 12 | no | 165000 |
| Ikat | 120 | no | 1600000 |

Meaning:

```text
1 batang = 12 meter
1 ikat = 120 meter
```

Another product may define `batang` differently.

Example:

```text
Hollow 4x4

1 batang = 6 meter
```

Therefore NEVER put `conversion_factor` in the global `units` table.

---

# 8. Quantity Calculation

All inventory calculations must normalize quantities to the product's base unit.

Formula:

```text
quantity_base = quantity × conversion_factor
```

Example:

```text
Sale:
2 batang

conversion:
1 batang = 12 meter

quantity_base:
2 × 12 = 24 meter
```

The inventory ledger decreases by 24 base units.

Use `DECIMAL`, not `FLOAT`, for quantities.

Recommended:

```text
DECIMAL(18,4)
```

This supports quantities such as:

```text
2.5 meter
0.75 kg
1.25 unit
```

---

# 9. Pricing Rules

Selling prices may differ by product unit.

Do not assume:

```text
harga batang = harga meter × conversion
```

because stores may use wholesale or package pricing.

Example:

```text
Meter    Rp15.000
Batang   Rp165.000
Ikat     Rp1.600.000
```

Use `DECIMAL`, not floating-point types, for money.

Recommended:

```text
DECIMAL(18,2)
```

Never use JavaScript floating-point arithmetic as the source of truth for final payment calculations.

Server-side Laravel calculations are authoritative.

---

# 10. Inventory Architecture

Do not make `products.stock` the sole source of inventory truth.

Use:

```text
stock_movements
```

for historical stock ledger entries and:

```text
inventory_balances
```

for fast current-stock lookup.

---

# 11. Stock Movements

Every stock change must have a traceable movement.

Suggested movement types:

```text
opening
purchase
sale
sale_return
purchase_return
adjustment_in
adjustment_out
transfer_in
transfer_out
```

Example:

| type | qty_base |
|---|---:|
| opening | +100 |
| sale | -12 |
| purchase | +50 |
| adjustment_out | -2 |

The movement should reference the originating transaction whenever possible.

Suggested fields:

```text
id
store_id
warehouse_id
location_id
product_id
type
reference_type
reference_id
quantity_base
balance_before
balance_after
notes
created_by
created_at
```

Historical stock data should be auditable.

Do not delete historical stock movements during normal business workflows.

Use reversals or compensating movements where appropriate.

---

# 12. Inventory Balances

Use a balance table for fast reads.

Suggested fields:

```text
inventory_balances
-------------------------
id
store_id
warehouse_id
product_id
quantity_base
updated_at
```

Recommended unique key:

```text
UNIQUE(store_id, warehouse_id, product_id)
```

If location-level inventory becomes necessary, extend the unique key with `location_id`.

The ledger remains the historical source, while `inventory_balances` is the current balance optimized for POS reads.

---

# 13. Minimum Stock / Stock Limit

Minimum stock must be normalized to the product's base unit.

Suggested product field:

```text
minimum_stock_base DECIMAL(18,4)
```

The UI may display minimum stock using a more convenient product unit.

Example:

```text
Base unit:
meter

Minimum stock:
240 meter

Display:
20 batang
```

when:

```text
1 batang = 12 meter
```

Dashboard states should include:

```text
Stok Aman
Stok Menipis
Stok Habis
```

---

# 14. Store / Warehouse Preparedness

Include `store_id` in the core design even if the first deployment only has one store.

This minimizes future migration pain if the business later adds:

```text
Toko Utama
Gudang
Cabang 2
```

Warehouse/location design should remain extensible.

Avoid hard-coding a single physical storage location into domain logic.

---

# 15. Product Locations

Products can have a physical location such as:

```text
Rak A-01
Rak B-04
Gudang Belakang
Blok C
```

Use a separate locations table.

Suggested:

```text
locations
-------------------------
id
store_id
warehouse_id
code
name
description
created_at
updated_at
```

The POS product search result should show location information when available.

---

# 16. Sales Architecture

Use separate tables for sales and sale items.

Suggested:

```text
sales
sale_items
payments
```

Do not combine the entire transaction into a single table.

---

# 17. Sales

Suggested sales fields:

```text
sales
-------------------------
id
store_id
invoice_number
customer_id
cashier_id
status
subtotal
discount_total
grand_total
notes
sold_at
created_at
updated_at
```

Possible statuses:

```text
draft
completed
void
refunded
```

Do not hard-delete completed sales.

---

# 18. Sale Item Snapshot

A sale item must preserve the historical state at the moment of sale.

Do not depend on the current product name, price, or conversion when rendering old transactions.

Suggested fields:

```text
sale_items
-------------------------
id
sale_id
product_id
product_code_snapshot
product_name_snapshot
unit_id
unit_name_snapshot
conversion_factor_snapshot
quantity
quantity_base
unit_price
discount_amount
subtotal
cost_snapshot
created_at
updated_at
```

Example:

If today's selling price is:

```text
Rp165.000 / batang
```

and six months later it becomes:

```text
Rp180.000 / batang
```

the old receipt must still display:

```text
Rp165.000 / batang
```

---

# 19. Payments and Turnover

Do NOT treat turnover (`omzet`) and incoming cash (`pemasukan`) as the same thing.

Example:

Today's sales:

```text
Rp10.000.000
```

Payment split:

```text
Cash       Rp3.000.000
Transfer   Rp4.000.000
Piutang    Rp3.000.000
```

Then:

```text
Omzet Hari Ini       Rp10.000.000
Uang Masuk Hari Ini   Rp7.000.000
Piutang                Rp3.000.000
```

If yesterday's receivable is paid today, that payment is:

```text
cash inflow today
```

but it is NOT:

```text
today's turnover
```

Keep these concepts separate in both database and dashboard calculations.

---

# 20. Payments Table

Suggested fields:

```text
payments
-------------------------
id
store_id
sale_id
payment_method
amount
reference_number
paid_at
received_by
notes
created_at
updated_at
```

Initial payment methods may include:

```text
cash
bank_transfer
qris
debit
credit
receivable
```

Do not hard-code the payment UI in a way that makes adding another payment method difficult.

---

# 21. POS Screen Goals

The POS page should prioritize speed.

Recommended desktop layout:

```text
┌─────────────────────────────────────────────────────────┐
│ Search Product / Scan Barcode                          │
├────────────────────────────────┬────────────────────────┤
│ Product Search / Product Info  │ Current Transaction    │
│                                │                        │
│ Code                           │ Product A              │
│ Name                           │ 2 × 165.000            │
│ Location                       │                        │
│ Current Stock                  │ Product B              │
│ Unit Selector                  │ 3 × 85.000             │
│ Price                          │                        │
│                                │ TOTAL                  │
│ [+ Create New Product]         │ [ PAY ]                │
└────────────────────────────────┴────────────────────────┘
```

---

# 22. POS Must Be Keyboard Friendly

Design POS workflows for keyboard use.

Suggested shortcuts:

```text
F2      Search product
Enter   Select / confirm
F4      Select unit
F8      Customer
F9      Payment
Esc     Close / cancel current modal
```

Exact shortcuts can evolve, but keyboard-first behavior is required.

Barcode scanners typically behave as keyboard input and should work naturally with the product-search input.

---

# 23. Frontend Responsibility Split

Use Alpine.js for small, immediate client-side interactions such as:

```text
modal visibility
focus management
keyboard shortcuts
dropdown behavior
temporary cart interaction
UI toggles
```

Use Livewire/Laravel for authoritative domain operations such as:

```text
product lookup
product creation
stock validation
checkout
payment
database writes
stock movement creation
dashboard queries
```

Do not make a network request for every trivial UI toggle.

Do not move authoritative business rules entirely into browser JavaScript.

---

# 24. Quick Create Product UX

The quick-create dialog should be optimized for speed.

Suggested form:

```text
Kode Barang      [________________]
Nama Barang      [________________]
Lokasi           [________________]
Satuan Dasar     [________________]
Harga Jual       [________________]
Stok Awal        [________________]
Minimum Stok     [________________]

[ Simpan & Masukkan ke Penjualan ]
```

After successful creation, the product must immediately become available to the current transaction.

Avoid page reloads.

---

# 25. Initial Database Tables

Build the database incrementally.

Recommended initial phase:

```text
stores
warehouses
locations
categories
units
products
product_units
inventory_balances
stock_movements
sales
sale_items
payments
customers
users
```

Purchasing can follow:

```text
suppliers
purchases
purchase_items
```

Do not create dozens of speculative tables before the associated functionality is implemented.

---

# 26. Recommended Migration Order

Use an order that respects foreign keys.

Example:

```text
1. stores
2. warehouses
3. locations
4. categories
5. units
6. products
7. product_units
8. inventory_balances
9. stock_movements
10. customers
11. sales
12. sale_items
13. payments
14. suppliers
15. purchases
16. purchase_items
```

Adapt the exact order to Laravel's existing user/auth migrations.

---

# 27. MySQL Data Type Rules

## IDs

Use:

```text
BIGINT UNSIGNED
```

through Laravel's standard:

```php
$table->id();
```

## Quantity

Use:

```text
DECIMAL(18,4)
```

## Money

Use:

```text
DECIMAL(18,2)
```

## Product Codes

Suggested:

```text
VARCHAR(50)
```

## Product Names

Suggested:

```text
VARCHAR(150)
```

Avoid JSON columns for core transactional fields that need relational querying.

---

# 28. Indexing Rules

At minimum, consider indexes for:

```text
products(store_id, code) UNIQUE
products(store_id, name)
product_units(product_id, unit_id) UNIQUE
inventory_balances(store_id, warehouse_id, product_id) UNIQUE
stock_movements(product_id, created_at)
sales(store_id, sold_at)
sales(invoice_number)
sale_items(sale_id)
payments(sale_id)
payments(paid_at)
```

Do not add indexes blindly.

Use indexes based on real query patterns.

---

# 29. Dashboard Requirements

Initial dashboard should support:

```text
Omzet Hari Ini
Uang Masuk Hari Ini
Jumlah Transaksi Hari Ini
Rata-rata Nilai Transaksi
Stok Menipis
Stok Habis
Produk Terlaris
Omzet 7 Hari
Omzet 30 Hari
Penjualan per Metode Pembayaran
```

Potential later reports:

```text
penjualan per kategori
penjualan per kasir
stock valuation
fast-moving products
slow-moving products
gross profit
receivables
stock card
```

Do not create a dashboard summary table immediately unless performance measurements justify it.

Initially calculate from transactional data with well-indexed queries.

A future `daily_summaries` aggregate table may be introduced if needed.

---

# 30. Inventory Adjustment

Stock adjustment must require:

```text
product
quantity
reason
user
timestamp
```

Examples:

```text
Barang rusak
Selisih stok opname
Barang hilang
Koreksi input
```

Every adjustment must produce a `stock_movements` record.

Never directly modify stock without a movement entry.

---

# 31. Opening Stock

When a product is created with opening stock:

Do NOT simply set `inventory_balances.quantity_base`.

Create:

```text
stock movement type = opening
```

and then update the inventory balance.

This preserves history from the first stock entry.

---

# 31.1 Purchasing & Goods Receiving from Supplier

Goods receiving from suppliers must record full transactional audit trails and automatically update stock:

1. **Suppliers**: Master table (`suppliers`) storing supplier details (`name`, `code`, `phone`, `address`).
2. **Purchase Header (`purchases`)**:
   - `invoice_supplier_number`: Supplier's invoice or delivery order number (Surat Jalan).
   - `supplier_id`: Reference to supplier.
   - `purchased_at`: Date/time of receipt.
   - `grand_total`: Total purchase cost.
   - `status`: `completed`, `draft`, `void`.
3. **Purchase Items (`purchase_items`)**:
   - Preserves snapshot of product, unit, conversion factor, received quantity, base quantity, buy price (*cost_price*), and subtotal.
4. **Stock Movement Integration**:
   - Every purchase item must atomically generate a `stock_movements` record with `type = purchase`.
   - Quantity is normalized to product base unit (`quantity_base = quantity × conversion_factor`).
   - `inventory_balances` must be incremented within `DB::transaction(...)` using `lockForUpdate()`.

---

# 31.2 User Roles & Notification System

## Roles & Authorization

The system supports two primary roles defined on the `users` table (`role` column):

1. **Admin**:
   - Full system access (Master Data, Stok Opname, Goods In / Purchasing Supplier, Reports, Management, Notification Settings).
2. **Kasir (Cashier)**:
   - Optimized access to POS Selling screen, Quick Create Product, Customer selection, Payment processing, and Low Stock alerts.

Use Laravel Gates/Policies to authorize role access across Livewire components and actions.

## Notifications

The system must support real-time notification alerts:

1. **New Sale Notification (`SaleCompleted`)**:
   - Triggered when a cashier completes a transaction.
   - Admin receives an alert: *"Penjualan Baru: Invoice #INV-xxx oleh Kasir [Nama] senilai Rp X"*.
2. **Low Stock Notification (`LowStockDetected`)**:
   - Triggered automatically during stock deduction when `quantity_base <= minimum_stock_base`.
   - Admin & Kasir receive an alert: *"Stok Menipis: [Nama Barang] tersisa X [Satuan]"*.
3. **UI & Audio Integration**:
   - Notification Bell in header with unread count badge.
   - Audio/Visual toast alerts (*sound effect ping*) on active Livewire sessions when events occur.

---

# 31.3 Fitur Operasional Khusus Toko Besi & Bahan Bangunan

1. **Hold / Pending Transaction (Simpan Transaksi Sementara)**:
   - Kasir dapat menahan *(hold)* keranjang belanjaan dengan pintasan keyboard (`F6`) saat pelanggan menambah barang/cek rak, melayani antrean lain, lalu memanggil kembali *(restore)* keranjang tersebut.
2. **Cetak Surat Jalan & Struk Kasir**:
   - Setelah transaksi selesai, kasir dapat mencetak **Struk Pembayaran** (untuk pelanggan) dan **Surat Jalan Pengiriman** (untuk pengemudi armada / bagian muat gudang).
3. **Manajemen Piutang Pelanggan & Alert Jatuh Tempo**:
   - Setiap pelanggan dapat memiliki `credit_limit` (batas maksimum piutang) dan `payment_terms_days` (jatuh tempo pembayaran).
   - Kasir/Admin diperingatkan jika transaksi kredit melampaui batas piutang.
   - Notifikasi otomatis jika piutang mendekati/melewati tanggal jatuh tempo.
4. **Log Perubahan Harga Jual & Harga Beli (`price_histories`)**:
   - Merekam setiap perubahan harga jual atau harga beli (*cost price*) produk beserta id user yang mengubah, waktu, dan alasan perubahan.

---

# 32. Product Search

POS product search should support:

```text
exact product code
partial product code
partial product name
barcode if configured
```

Rank exact code matches ahead of fuzzy / partial name results.

Examples:

```text
B10
besi 10
hollow
899123...
```

Search must remain fast as the product catalog grows.

Use debouncing carefully; exact code / barcode actions should feel immediate.

---

# 33. Deletion Rules

Never casually hard-delete transactional data.

Recommended behavior:

```text
products        soft delete or deactivate
sales           void instead of hard delete
stock movements never routinely delete
payments        reverse/correct with audit trail
units           prevent deletion when used
```

Master records used by historical transactions should remain resolvable.

---

# 34. Auditability

Important actions should preserve:

```text
created_by
updated_by where useful
timestamps
reason / notes for adjustments
transaction references
```

Potential future audit log:

```text
activity_logs
```

Do not implement an overly complex audit package during the initial MVP unless requested.

---

# 35. Validation

Validate all business-critical inputs server-side.

Examples:

Product:

```text
code                required
name                required
base unit           required
conversion          > 0
selling price       >= 0
opening stock       >= 0
minimum stock       >= 0
```

Sale item:

```text
quantity            > 0
selected unit       valid for product
conversion factor   valid
price                >= 0
```

Payment:

```text
amount              > 0
payment method      valid
```

Do not trust browser-side validation alone.

---

# 36. Decimal and Currency Display

Database values should remain numeric.

Formatting is a presentation concern.

Indonesian currency example:

```text
Rp 165.000
Rp 1.600.000
```

Quantity example:

```text
2,5 meter
```

Do not store formatted strings such as:

```text
"Rp 165.000"
```

in numeric database fields.

---

# 37. Testing Priorities

Tests are especially important for inventory and checkout.

At minimum write tests for:

## Unit Conversion

```text
2 batang × 12 = 24 meter
```

## Stock Deduction

```text
100 base units
sale 24
remaining 76
```

## Multi-unit Pricing

Ensure selected unit price is used correctly.

## Product Code Uniqueness

Duplicate code within the same store must fail.

## Checkout Atomicity

If payment or stock update fails, no partial sale should remain.

## Concurrent Stock

Two simultaneous checkouts must not oversell limited stock.

## Historical Snapshot

Changing a product price must not modify old sale-item values.

## Turnover vs Incoming Payments

Old receivable payment must not be counted as today's sales turnover.

---

# 38. Coding Style

Follow Laravel conventions.

Use:

```text
Form Requests where useful
Enums for stable status/type values when beneficial
Service / Action classes for domain operations
Eloquent relationships
database foreign keys
PHP type declarations
Laravel validation
Laravel authorization
```

Avoid:

```text
raw SQL everywhere
business logic in Blade
business logic in Alpine
duplicated stock calculations
magic strings repeated across the application
massive controllers
massive Livewire components
```

Keep code easy to read and maintain.

---

# 39. Naming

Use English for code identifiers:

```text
Product
ProductUnit
StockMovement
InventoryBalance
Sale
SaleItem
Payment
Location
Unit
```

Use Indonesian for user-facing text:

```text
Barang
Satuan
Lokasi
Stok
Penjualan
Pembayaran
Omzet
Pemasukan
```

Do not mix Indonesian and English inconsistently in PHP class/table/column names.

---

# 40. UI Style Direction

The POS interface should feel:

```text
fast
clean
professional
high contrast
easy to scan
desktop-first but responsive
usable on tablet
```

Prioritize usability over decorative design.

Use Tailwind utility classes.

Avoid unnecessary animations in the POS workflow.

Important information such as:

```text
stock
price
selected unit
total
payment status
```

must be visually obvious.

---

# 41. Accessibility

Interactive elements must be keyboard accessible.

Use proper labels.

Do not rely exclusively on color to communicate stock status.

Example:

```text
Stok Menipis — 5 batang
```

rather than only changing the text color.

---

# 42. Security

Use Laravel's standard protections:

```text
CSRF
authentication
authorization
validation
escaped Blade output
mass-assignment protection
database transactions
```

Never expose database credentials to frontend code.

Do not trust cashier-submitted prices, conversion factors, totals, or stock balances without server verification.

---

# 43. Performance Principles

Do not prematurely optimize, but avoid obvious N+1 queries.

Use:

```text
eager loading
proper indexes
pagination
limited product-search result sets
aggregate SQL queries
```

Do not load the entire product catalog into the browser just to provide POS search.

---

# 44. Out of Scope for Initial MVP

Do not implement these unless explicitly requested:

```text
offline-first POS
PWA synchronization
multi-device offline reconciliation
complex accounting/general ledger
full payroll
e-commerce
marketplace integration
WhatsApp automation
advanced loyalty system
AI forecasting
complex production/manufacturing
```

Design the core cleanly enough that future integrations remain possible.

---

# 45. Optional Future Feature: Cut-Length / Remnant Stock

Hardware stores may sell only part of a long item.

Example:

```text
1 batang hollow = 6 meter
customer buys 2.5 meter
remaining physical piece = 3.5 meter
```

The base-unit stock calculation already supports this.

However, tracking individual physical remnants such as:

```text
3.5 m
2 m
1.5 m
```

requires a separate lot/remnant model.

Do NOT implement remnant tracking in the initial MVP unless specifically requested.

---

# 46. Development Workflow for the Agent

Before making changes:

1. Inspect the existing project structure.
2. Inspect `composer.json`.
3. Inspect `package.json`.
4. Inspect existing migrations and models.
5. Check the installed Laravel and Livewire versions.
6. Reuse existing conventions.
7. Do not overwrite working code without justification.

For each feature:

1. understand the business rule;
2. design database changes;
3. create/update migrations;
4. create models and relationships;
5. implement domain service/action;
6. implement Livewire UI;
7. validate server-side;
8. add tests for critical business logic;
9. run relevant tests;
10. verify no existing feature is broken.

---

# 47. Required Commands

Typical local commands:

```bash
composer install
npm install
php artisan migrate
composer run dev
```

Livewire component:

```bash
php artisan make:livewire ComponentName
```

Model with migration:

```bash
php artisan make:model Product -m
```

Test:

```bash
php artisan test
```

Code style if Laravel Pint is available:

```bash
./vendor/bin/pint
```

On Windows PowerShell / Git Bash, use the equivalent executable invocation if needed.

---

# 48. First Implementation Milestone

When beginning development, implement in this order:

## Phase 1 — Master Data

```text
stores
warehouses
locations
categories
units
products
product_units
```

Create:

```text
Unit management
Product management
Product unit/conversion management
Location management
```

## Phase 2 — Inventory

```text
inventory_balances
stock_movements
opening stock
stock adjustment
minimum stock
```

## Phase 3 — POS

```text
product search
product quick create
unit selection
cart
stock validation
checkout
payments
receipt data
```

## Phase 4 — Dashboard

```text
daily turnover
daily incoming payments
transaction count
low stock
out of stock
top-selling products
```

## Phase 5 — Purchasing

```text
suppliers
purchases
purchase_items
stock receiving
```

Do not jump to advanced reports before the transactional foundation is reliable.

---

# 49. Non-Negotiable Domain Rules

The following decisions are already approved and must not be changed without explicit instruction:

1. Database is **MySQL**.
2. Backend framework is **Laravel 13**.
3. Interactive frontend is **Livewire 4**.
4. Styling is **Tailwind CSS 4**.
5. Lightweight frontend behavior uses **Alpine.js**.
6. Product code is **manual**, not auto-generated.
7. Product code must be unique per store.
8. Units use a separate `units` master table.
9. Conversion factors belong to `product_units`, not `units`.
10. One product may have multiple selling units.
11. One product must have one base unit.
12. Inventory is normalized to base-unit quantity.
13. Prices may differ by unit.
14. Products may be created directly while entering a sale.
15. Every stock change must create a stock movement.
16. `inventory_balances` is used for fast current-stock lookup.
17. Checkout must be atomic.
18. Completed historical transaction values must be preserved using snapshots.
19. Turnover and incoming payments are different concepts.
20. The POS screen must prioritize speed and keyboard usability.
21. Incoming goods from suppliers must record purchase transactions (`purchases` & `purchase_items`) and increment stock via `stock_movements` with type `purchase`.

---

# 50. Definition of Done

A feature is not complete merely because the UI renders.

A feature is considered done when:

```text
database design is valid
business rule is implemented
server validation exists
authorization is respected where applicable
stock/accounting side effects are correct
transaction boundaries are safe
UI states are handled
errors are user-friendly
critical tests pass
code follows project conventions
```

When uncertain about a business rule, do not silently invent a major behavior.

Prefer asking for clarification before changing one of the non-negotiable domain rules above.
