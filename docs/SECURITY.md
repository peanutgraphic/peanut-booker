# Security Documentation

## Overview

Peanut Booker implements comprehensive security measures to protect booking data, financial transactions, and user privacy.

## Authentication & Authorization

### Role-Based Access Control

Peanut Booker defines custom WordPress roles with specific capabilities:

| Role | Capabilities |
|------|-------------|
| `pb_performer` | Edit own profile, view bookings, manage availability, respond to reviews |
| `pb_customer` | Book performers, create events, leave reviews |
| Administrator | Full access to all Booker features |

### Capability-Based Permission Checks

The `Peanut_Booker_Roles` class provides centralized permission checking:

```php
// Check if user can view a booking
Peanut_Booker_Roles::can_view_booking($booking, $user_id);

// Check if user can review a booking
Peanut_Booker_Roles::can_review_booking($booking, $user_id);

// Check if user can edit a performer profile
Peanut_Booker_Roles::can_edit_performer($performer, $user_id);
```

### Authorization Hierarchy

1. **Admin Override**: Users with `pb_manage_bookings` can access any booking
2. **Ownership Check**: Customer or performer involved in booking
3. **Deny by Default**: All other access denied

## REST API Security

### Endpoint Protection

| Endpoint Type | Protection |
|--------------|------------|
| Public (listings) | Rate limiting, input validation |
| Authenticated | WordPress auth + capability check |
| Admin | `manage_options` or specific capability |

### Rate Limiting

```php
// Rate limit configuration
$limiter = new Peanut_Booker_Rate_Limiter('booking_create', 10, 60);

if (!$limiter->check()) {
    return new WP_Error('rate_limit', 'Too many requests', ['status' => 429]);
}
```

### AJAX Request Security

All AJAX handlers verify:

```php
// Nonce verification
check_ajax_referer('pb_booking_action', 'nonce');

// Capability check
if (!current_user_can('pb_book_performers')) {
    wp_die('Unauthorized', 403);
}
```

## Payment Security

### Encryption

Sensitive payment data uses AES-256-GCM encryption:

```php
class Peanut_Booker_Encryption {
    // Encryption key derived from WordPress salts
    // Never stored in database

    public static function encrypt(string $data): string;
    public static function decrypt(string $encrypted): string;
}
```

### Key Management

- Encryption key derived from `SECURE_AUTH_KEY` WordPress constant
- Keys never stored in database
- If WordPress salts change, data re-encryption required

### WooCommerce Integration

Checkout security measures:

```php
// Verify order belongs to current user
if ($order->get_customer_id() !== get_current_user_id()) {
    return; // Silently reject
}

// Verify booking exists and matches order
$booking = Peanut_Booker_Booking::get($booking_id);
if (!$booking || $booking->order_id !== $order_id) {
    return;
}
```

## Input Validation

### Booking Data

| Field | Validation |
|-------|------------|
| `event_date` | Valid date, not in past |
| `event_location` | `sanitize_text_field()`, max 255 chars |
| `total_amount` | Positive float, max 2 decimals |
| `performer_id` | Exists and is approved |

### Review Submission

```php
// Rating validation
$rating = absint($rating);
if ($rating < 1 || $rating > 5) {
    return new WP_Error('invalid_rating', 'Rating must be 1-5');
}

// Content sanitization
$content = wp_kses_post($content);
```

## SQL Injection Prevention

All database queries use prepared statements:

```php
$wpdb->prepare(
    "SELECT * FROM {$table} WHERE performer_id = %d AND booking_status = %s",
    $performer_id,
    $status
);
```

### Dynamic Column Names

When column names must be dynamic, they're validated against a whitelist:

```php
$allowed_columns = ['created_at', 'event_date', 'total_amount'];
$orderby = in_array($orderby, $allowed_columns, true) ? $orderby : 'created_at';
```

## CSRF Protection

### Form Submissions

All forms include WordPress nonces:

```php
// Generate nonce
wp_nonce_field('pb_booking_action', 'pb_nonce');

// Verify nonce
if (!wp_verify_nonce($_POST['pb_nonce'], 'pb_booking_action')) {
    wp_die('Invalid request');
}
```

### REST API

REST endpoints use WordPress built-in nonce verification for authenticated requests.

## Data Privacy

### Personal Data

| Data Type | Storage | Access |
|-----------|---------|--------|
| Customer details | Encrypted where sensitive | Owner + Admin |
| Performer profiles | Public (published) | Public |
| Booking history | Plain text | Parties + Admin |
| Payment info | Encrypted | Owner only |

### Data Export (GDPR)

```php
// Register personal data exporter
add_filter('wp_privacy_personal_data_exporters', function($exporters) {
    $exporters['peanut-booker'] = [
        'exporter_friendly_name' => 'Peanut Booker',
        'callback' => [Peanut_Booker_Privacy::class, 'export_personal_data'],
    ];
    return $exporters;
});
```

### Data Erasure (GDPR)

Booking data can be anonymized while preserving financial records.

## Review Security

### Fraud Prevention

- One review per booking per party
- Review only after booking completed
- Content moderation via flagging system

### Arbitration

Disputed reviews go through:
1. Flag submission with reason
2. Admin review
3. Arbitration decision (uphold, modify, remove)
4. Both parties notified

## Security Event Logging

```php
Peanut_Booker_Logger::log('security', 'unauthorized_access_attempt', [
    'user_id' => get_current_user_id(),
    'resource' => 'booking',
    'resource_id' => $booking_id,
    'ip' => $_SERVER['REMOTE_ADDR'],
]);
```

## Security Headers

The plugin sets security headers on AJAX responses:

```php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
```

## Reporting Security Issues

Report vulnerabilities to: security@peanutgraphic.com

Please include:
- Detailed description
- Steps to reproduce
- Potential impact assessment
- Suggested remediation (if any)

## Security Checklist

### For Site Owners

- [ ] Keep WordPress and all plugins updated
- [ ] Use strong admin passwords
- [ ] Enable HTTPS site-wide
- [ ] Regular database backups
- [ ] Monitor failed login attempts

### For Developers

- [ ] All input validated and sanitized
- [ ] Capability checks on all protected actions
- [ ] Nonces used for state-changing operations
- [ ] Prepared statements for all queries
- [ ] Sensitive data encrypted
