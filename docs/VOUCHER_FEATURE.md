# Task: Code Vouchers Feature

## Overview
Implement a system for promotional codes (vouchers) that allow customers to receive discounts on their orders. Vouchers are independent of the referral system and are used exclusively for price reductions.

## 🏗️ Architecture

### 1. Domain & Models
**Domain:** `src/Domain/Ordering`

#### Voucher Model
**Location:** `src/Domain/Ordering/Models/Voucher.php`
- `id` (int)
- `event_id` (int, foreign)
- `code` (string, unique per event, uppercase)
- `type` (enum: fixed, percentage)
- `value` (decimal)
- `min_order_amount` (decimal, optional)
- `max_discount_amount` (decimal, optional, for percentage vouchers)
- `total_limit` (int, optional, max uses)
- `used_count` (int, default 0)
- `starts_at` (datetime, optional)
- `ends_at` (datetime, optional)
- `is_active` (boolean, default true)
- `created_at` / `updated_at`

### 2. Database
- Create migration: `create_vouchers_table`
- Add `voucher_id`, `voucher_discount_amount`, and `voucher_snapshot` (JSON) to `orders` table.

### 3. Services & Price Calculation
- Update `PriceCalculationService` to accept an optional `voucherCode`.
- **Discount Logic:** Apply discount to the base subtotal *before* calculating taxes and fees. Taxes should be calculated on the discounted amount.
- Update `PriceBreakdown` DTO to include `voucherDiscount` and `voucherId`.

### 4. Actions
- `ValidateVoucher`: Checks if a code is valid for an event, date, and order amount.
- `ApplyVoucherToOrder`: Increments usage and saves snapshot during order creation.

## 🚀 Tasks

### Phase 1: Backend Infrastructure
- [x] Create `VoucherType` enum.
- [x] Create `vouchers` table migration.
- [x] Create `Voucher` model with `event` relationship.
- [x] Update `orders` table with voucher columns.

### Phase 2: Price Calculation Integration
- [x] Update `PriceBreakdown` DTO.
- [x] Modify `PriceCalculationService` to subtract discount from subtotal before tax calculation.
- [x] Update `OrderService` to handle voucher persistence.
- [x] Add unit tests for various voucher types (fixed vs percentage).

### Phase 3: Management API
- [ ] Implement CRUD in `ManageEventController`.
- [ ] Add public validation endpoint `POST /api/events/{event}/vouchers/validate`.

### Phase 4: UI Implementation
- [ ] **Organizer Dashboard:** Complete the `Vouchers.vue` page with a management table and creation form.
- [ ] **Checkout:** Add the voucher input field and integrate with the price calculation real-time update.

## 📝 Notes
- Codes must be stored and compared as uppercase.
- Vouchers are strictly for discounts; they do not affect promoter commissions.
