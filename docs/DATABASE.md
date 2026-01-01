# Database Schema Documentation

## Overview

Peanut Booker uses a multi-table database schema to manage performers, bookings, reviews, marketplace events, and financial transactions.

## Table Prefix

All tables use the WordPress table prefix (typically `wp_`) followed by `pb_`.

## Entity Relationship Diagram

```
┌─────────────────┐       ┌──────────────────┐
│  pb_performers  │───1:N─│   pb_bookings    │
└────────┬────────┘       └────────┬─────────┘
         │                         │
         │1:N                      │1:N
         ▼                         ▼
┌─────────────────┐       ┌──────────────────┐
│ pb_availability │       │   pb_reviews     │
└─────────────────┘       └──────────────────┘
                                   │
                                   │1:N
                                   ▼
                          ┌──────────────────┐
                          │ pb_transactions  │
                          └──────────────────┘

┌─────────────────┐       ┌──────────────────┐
│   pb_events     │───1:N─│     pb_bids      │
└─────────────────┘       └──────────────────┘

┌─────────────────┐       ┌──────────────────┐
│ pb_performers   │───1:1─│  pb_microsites   │
└─────────────────┘       └──────────────────┘

┌─────────────────────┐
│   pb_subscriptions  │───N:1──[WordPress Users]
└─────────────────────┘

┌─────────────────────┐
│ pb_sponsored_slots  │───N:1──[pb_performers]
└─────────────────────┘
```

---

## Core Tables

### pb_performers

Primary table for performer profiles and metrics.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary key |
| `user_id` | BIGINT UNSIGNED | WordPress user ID (unique) |
| `profile_id` | BIGINT UNSIGNED | Associated WP post ID |
| `tier` | VARCHAR(20) | 'free', 'pro' (subscription tier) |
| `achievement_level` | VARCHAR(20) | 'bronze', 'silver', 'gold', 'platinum' |
| `achievement_score` | INT | Cumulative score for gamification |
| `completed_bookings` | INT | Total completed bookings |
| `average_rating` | DECIMAL(3,2) | Average review rating |
| `total_reviews` | INT | Count of reviews received |
| `profile_completeness` | INT(3) | Percentage 0-100 |
| `hourly_rate` | DECIMAL(10,2) | Base hourly rate |
| `deposit_percentage` | INT(3) | Deposit % (default 25) |
| `minimum_deposit` | DECIMAL(10,2) | Minimum deposit amount |
| `service_radius` | INT | Miles willing to travel |
| `is_verified` | TINYINT(1) | Identity verified flag |
| `is_featured` | TINYINT(1) | Featured performer flag |
| `status` | VARCHAR(20) | 'pending', 'approved', 'suspended' |
| `created_at` | DATETIME | Creation timestamp |
| `updated_at` | DATETIME | Last update timestamp |

**Indexes:**
- `unique_user_id` (user_id)
- `idx_profile_id` (profile_id)
- `idx_tier` (tier)
- `idx_achievement_level` (achievement_level)
- `idx_status` (status)

---

### pb_bookings

Tracks all bookings between customers and performers.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary key |
| `booking_number` | VARCHAR(32) | Unique human-readable ID |
| `performer_id` | BIGINT UNSIGNED | FK to performers |
| `customer_id` | BIGINT UNSIGNED | WordPress user ID |
| `event_id` | BIGINT UNSIGNED | FK to events (if from marketplace) |
| `bid_id` | BIGINT UNSIGNED | FK to bids (if from bid) |
| `order_id` | BIGINT UNSIGNED | WooCommerce order ID |
| `event_title` | VARCHAR(255) | Event name |
| `event_description` | TEXT | Event details |
| `event_date` | DATE | Event date |
| `event_start_time` | TIME | Start time |
| `event_end_time` | TIME | End time |
| `event_location` | VARCHAR(255) | Venue name |
| `event_address` | TEXT | Full address |
| `event_city` | VARCHAR(100) | City |
| `event_state` | VARCHAR(100) | State |
| `event_zip` | VARCHAR(255) | Postal code |
| `total_amount` | DECIMAL(10,2) | Total booking value |
| `deposit_amount` | DECIMAL(10,2) | Deposit required |
| `remaining_amount` | DECIMAL(10,2) | Balance due |
| `platform_commission` | DECIMAL(10,2) | Platform fee |
| `performer_payout` | DECIMAL(10,2) | Performer earnings |
| `deposit_paid` | TINYINT(1) | Deposit received flag |
| `fully_paid` | TINYINT(1) | Full payment flag |
| `escrow_status` | VARCHAR(20) | 'pending', 'held', 'released' |
| `booking_status` | VARCHAR(20) | 'pending', 'confirmed', 'completed', 'cancelled' |
| `performer_confirmed` | TINYINT(1) | Performer accepted flag |
| `customer_confirmed_completion` | TINYINT(1) | Customer confirmed done |
| `completion_date` | DATETIME | When event completed |
| `payout_date` | DATETIME | When performer paid |
| `cancellation_reason` | TEXT | If cancelled, reason |
| `cancelled_by` | BIGINT UNSIGNED | User who cancelled |
| `notes` | TEXT | Internal notes |
| `created_at` | DATETIME | Creation timestamp |
| `updated_at` | DATETIME | Last update timestamp |

**Indexes:**
- `unique_booking_number` (booking_number)
- `idx_performer_id` (performer_id)
- `idx_customer_id` (customer_id)
- `idx_event_id` (event_id)
- `idx_order_id` (order_id)
- `idx_event_date` (event_date)
- `idx_booking_status` (booking_status)
- `idx_escrow_status` (escrow_status)

---

### pb_reviews

Bidirectional review system for performers and customers.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary key |
| `booking_id` | BIGINT UNSIGNED | FK to bookings |
| `reviewer_id` | BIGINT UNSIGNED | WordPress user ID |
| `reviewee_id` | BIGINT UNSIGNED | WordPress user ID |
| `reviewer_type` | VARCHAR(20) | 'performer' or 'customer' |
| `rating` | TINYINT(1) | 1-5 stars |
| `title` | VARCHAR(255) | Review headline |
| `content` | TEXT | Review body |
| `response` | TEXT | Reviewee's response |
| `response_date` | DATETIME | When response added |
| `is_flagged` | TINYINT(1) | Flagged for review |
| `flag_reason` | TEXT | Why flagged |
| `flagged_by` | BIGINT UNSIGNED | User who flagged |
| `flagged_date` | DATETIME | When flagged |
| `arbitration_status` | VARCHAR(20) | 'pending', 'upheld', 'removed' |
| `arbitration_notes` | TEXT | Admin notes |
| `arbitrated_by` | BIGINT UNSIGNED | Admin user ID |
| `arbitration_date` | DATETIME | When decided |
| `is_visible` | TINYINT(1) | Publicly visible flag |
| `created_at` | DATETIME | Creation timestamp |
| `updated_at` | DATETIME | Last update timestamp |

**Indexes:**
- `idx_booking_id` (booking_id)
- `idx_reviewer_id` (reviewer_id)
- `idx_reviewee_id` (reviewee_id)
- `idx_reviewer_type` (reviewer_type)
- `idx_reviewee_type` (reviewee_id, reviewer_type) - composite index for queries
- `idx_is_flagged` (is_flagged)
- `idx_is_visible` (is_visible)

---

## Marketplace Tables

### pb_events

Customer-posted events seeking performers.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary key |
| `customer_id` | BIGINT UNSIGNED | WordPress user ID |
| `post_id` | BIGINT UNSIGNED | Associated WP post |
| `title` | VARCHAR(255) | Event title |
| `description` | TEXT | Event details |
| `event_date` | DATE | Event date |
| `event_start_time` | TIME | Start time |
| `event_end_time` | TIME | End time |
| `location` | VARCHAR(255) | Venue name |
| `address` | TEXT | Full address |
| `city` | VARCHAR(100) | City |
| `state` | VARCHAR(100) | State |
| `zip` | VARCHAR(20) | Postal code |
| `category_id` | BIGINT UNSIGNED | Event category |
| `budget_min` | DECIMAL(10,2) | Minimum budget |
| `budget_max` | DECIMAL(10,2) | Maximum budget |
| `bid_deadline` | DATETIME | When bidding closes |
| `auto_deadline_days` | INT(3) | Days before event to close bids |
| `total_bids` | INT | Count of bids received |
| `accepted_bid_id` | BIGINT UNSIGNED | Winning bid FK |
| `status` | VARCHAR(20) | 'open', 'closed', 'cancelled' |
| `is_featured` | TINYINT(1) | Featured listing flag |
| `created_at` | DATETIME | Creation timestamp |
| `updated_at` | DATETIME | Last update timestamp |

**Indexes:**
- `idx_customer_id` (customer_id)
- `idx_post_id` (post_id)
- `idx_event_date` (event_date)
- `idx_category_id` (category_id)
- `idx_status` (status)
- `idx_bid_deadline` (bid_deadline)

---

### pb_bids

Performer bids on marketplace events.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary key |
| `event_id` | BIGINT UNSIGNED | FK to events |
| `performer_id` | BIGINT UNSIGNED | FK to performers |
| `bid_amount` | DECIMAL(10,2) | Proposed price |
| `message` | TEXT | Pitch to customer |
| `status` | VARCHAR(20) | 'pending', 'accepted', 'rejected' |
| `is_read` | TINYINT(1) | Customer viewed flag |
| `created_at` | DATETIME | Creation timestamp |
| `updated_at` | DATETIME | Last update timestamp |

**Indexes:**
- `unique_event_performer` (event_id, performer_id)
- `idx_event_id` (event_id)
- `idx_performer_id` (performer_id)
- `idx_status` (status)

---

## Scheduling Tables

### pb_availability

Performer calendar and availability management.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary key |
| `performer_id` | BIGINT UNSIGNED | FK to performers |
| `date` | DATE | Calendar date |
| `slot_type` | VARCHAR(20) | 'full_day', 'morning', 'afternoon', 'evening', 'custom' |
| `start_time` | TIME | Slot start (for custom) |
| `end_time` | TIME | Slot end (for custom) |
| `status` | VARCHAR(20) | 'available', 'blocked', 'booked' |
| `booking_id` | BIGINT UNSIGNED | FK to bookings (if booked) |
| `block_type` | VARCHAR(20) | 'manual', 'recurring', 'sync' |
| `event_name` | VARCHAR(255) | External event name |
| `venue_name` | VARCHAR(255) | External venue |
| `event_type` | VARCHAR(100) | Type of event |
| `event_location` | VARCHAR(255) | Location |
| `notes` | VARCHAR(255) | Additional notes |
| `created_at` | DATETIME | Creation timestamp |
| `updated_at` | DATETIME | Last update timestamp |

**Indexes:**
- `unique_performer_date_slot` (performer_id, date, slot_type, start_time)
- `idx_performer_id` (performer_id)
- `idx_date` (date)
- `idx_status` (status)
- `idx_booking_id` (booking_id)
- `idx_block_type` (block_type)

---

## Financial Tables

### pb_transactions

All financial transactions related to bookings.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary key |
| `booking_id` | BIGINT UNSIGNED | FK to bookings |
| `order_id` | BIGINT UNSIGNED | WooCommerce order ID |
| `transaction_type` | VARCHAR(30) | 'deposit', 'balance', 'payout', 'refund', 'commission' |
| `amount` | DECIMAL(10,2) | Transaction amount |
| `payment_method` | VARCHAR(50) | Payment method used |
| `payment_id` | VARCHAR(255) | External payment reference |
| `payer_id` | BIGINT UNSIGNED | User who paid |
| `payee_id` | BIGINT UNSIGNED | User who received |
| `status` | VARCHAR(20) | 'pending', 'completed', 'failed' |
| `notes` | TEXT | Transaction notes |
| `created_at` | DATETIME | Creation timestamp |
| `updated_at` | DATETIME | Last update timestamp |

**Indexes:**
- `idx_booking_id` (booking_id)
- `idx_order_id` (order_id)
- `idx_transaction_type` (transaction_type)
- `idx_status` (status)
- `idx_payer_id` (payer_id)
- `idx_payee_id` (payee_id)

---

### pb_subscriptions

Pro membership subscriptions for performers.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary key |
| `user_id` | BIGINT UNSIGNED | WordPress user ID |
| `wc_subscription_id` | BIGINT UNSIGNED | WooCommerce Subscriptions ID |
| `plan_type` | VARCHAR(20) | 'monthly', 'yearly' |
| `status` | VARCHAR(20) | 'active', 'cancelled', 'expired' |
| `start_date` | DATETIME | Subscription start |
| `end_date` | DATETIME | Subscription end |
| `next_billing_date` | DATETIME | Next renewal |
| `amount` | DECIMAL(10,2) | Subscription price |
| `payment_method` | VARCHAR(50) | Payment method |
| `created_at` | DATETIME | Creation timestamp |
| `updated_at` | DATETIME | Last update timestamp |

**Indexes:**
- `idx_user_id` (user_id)
- `idx_wc_subscription_id` (wc_subscription_id)
- `idx_status` (status)
- `idx_end_date` (end_date)

---

## Communication Tables

### pb_messages

Direct messaging between users.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary key |
| `sender_id` | BIGINT UNSIGNED | WordPress user ID |
| `recipient_id` | BIGINT UNSIGNED | WordPress user ID |
| `message` | TEXT | Message content |
| `booking_id` | BIGINT UNSIGNED | Related booking (optional) |
| `is_read` | TINYINT(1) | Read status |
| `created_at` | DATETIME | Creation timestamp |

**Indexes:**
- `idx_sender_id` (sender_id)
- `idx_recipient_id` (recipient_id)
- `idx_booking_id` (booking_id)
- `idx_created_at` (created_at)
- `idx_conversation` (sender_id, recipient_id, created_at) - composite

---

## Monetization Tables

### pb_sponsored_slots

Paid promotion slots for performers.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary key |
| `performer_id` | BIGINT UNSIGNED | FK to performers |
| `order_id` | BIGINT UNSIGNED | WooCommerce order ID |
| `slot_type` | VARCHAR(30) | 'homepage', 'category', 'search' |
| `position` | INT(3) | Display position |
| `start_date` | DATETIME | Promotion start |
| `end_date` | DATETIME | Promotion end |
| `amount_paid` | DECIMAL(10,2) | Price paid |
| `status` | VARCHAR(20) | 'active', 'expired', 'cancelled' |
| `impressions` | INT | View count |
| `clicks` | INT | Click count |
| `created_at` | DATETIME | Creation timestamp |
| `updated_at` | DATETIME | Last update timestamp |

**Indexes:**
- `idx_performer_id` (performer_id)
- `idx_slot_type` (slot_type)
- `idx_status` (status)
- `idx_start_date` (start_date)
- `idx_end_date` (end_date)

---

### pb_microsites

Performer-branded landing pages.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary key |
| `performer_id` | BIGINT UNSIGNED | FK to performers |
| `user_id` | BIGINT UNSIGNED | WordPress user ID |
| `subscription_id` | BIGINT UNSIGNED | FK to subscriptions |
| `status` | VARCHAR(20) | 'pending', 'active', 'suspended' |
| `slug` | VARCHAR(100) | URL slug (unique) |
| `custom_domain` | VARCHAR(255) | Custom domain if used |
| `domain_verified` | TINYINT(1) | Domain verification status |
| `has_custom_domain_addon` | TINYINT(1) | Paid addon flag |
| `design_settings` | TEXT | JSON design configuration |
| `meta_title` | VARCHAR(255) | SEO title |
| `meta_description` | TEXT | SEO description |
| `view_count` | INT | Total page views |
| `created_at` | DATETIME | Creation timestamp |
| `updated_at` | DATETIME | Last update timestamp |

**Indexes:**
- `unique_slug` (slug)
- `idx_performer_id` (performer_id)
- `idx_user_id` (user_id)
- `idx_status` (status)

---

## Maintenance

### Cleanup Queries

```sql
-- Delete old messages (> 1 year)
DELETE FROM wp_pb_messages
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Archive completed bookings (> 2 years)
-- (Consider archiving to separate table instead)
UPDATE wp_pb_bookings
SET notes = CONCAT_WS('\n', notes, 'ARCHIVED')
WHERE booking_status = 'completed'
AND completion_date < DATE_SUB(NOW(), INTERVAL 2 YEAR);

-- Clean expired sponsored slots
DELETE FROM wp_pb_sponsored_slots
WHERE status = 'expired'
AND end_date < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

### Index Optimization

Recommended periodic maintenance:

```sql
ANALYZE TABLE wp_pb_performers;
ANALYZE TABLE wp_pb_bookings;
ANALYZE TABLE wp_pb_reviews;
ANALYZE TABLE wp_pb_availability;
ANALYZE TABLE wp_pb_transactions;
```
