# Developer Extension Guide

## Overview

Peanut Booker is designed to be extensible through WordPress hooks, filters, and APIs. This guide covers how to extend Booker functionality.

## Architecture

```
peanut-booker/
├── includes/
│   ├── class-peanut-booker.php      # Main plugin class
│   ├── class-activator.php          # Installation/activation
│   ├── class-roles.php              # Custom roles & capabilities
│   ├── api/                         # REST API controllers
│   ├── models/                      # Data models
│   └── services/                    # Business logic
├── public/
│   ├── class-public.php             # Frontend functionality
│   └── templates/                   # Template files
└── admin/
    ├── class-admin.php              # Admin functionality
    └── views/                       # Admin views
```

---

## Action Hooks

### Performer Lifecycle

```php
/**
 * Fired when a performer profile is created
 *
 * @param int   $performer_id Performer ID
 * @param array $data         Performer data
 */
do_action('pb_performer_created', $performer_id, $data);

/**
 * Fired when a performer is approved
 *
 * @param int $performer_id Performer ID
 * @param int $approved_by  Admin user ID
 */
do_action('pb_performer_approved', $performer_id, $approved_by);

/**
 * Fired when a performer is suspended
 *
 * @param int    $performer_id Performer ID
 * @param string $reason       Suspension reason
 */
do_action('pb_performer_suspended', $performer_id, $reason);

/**
 * Fired when performer tier changes
 *
 * @param int    $performer_id Performer ID
 * @param string $old_tier     Previous tier
 * @param string $new_tier     New tier
 */
do_action('pb_performer_tier_changed', $performer_id, $old_tier, $new_tier);
```

### Booking Lifecycle

```php
/**
 * Fired when a booking is created
 *
 * @param int $booking_id Booking ID
 * @param int $performer_id Performer ID
 * @param int $customer_id Customer ID
 */
do_action('pb_booking_created', $booking_id, $performer_id, $customer_id);

/**
 * Fired when performer confirms a booking
 *
 * @param int $booking_id Booking ID
 */
do_action('pb_booking_confirmed', $booking_id);

/**
 * Fired when a booking is completed
 *
 * @param int $booking_id Booking ID
 */
do_action('pb_booking_completed', $booking_id);

/**
 * Fired when a booking is cancelled
 *
 * @param int    $booking_id Booking ID
 * @param int    $cancelled_by User ID who cancelled
 * @param string $reason Cancellation reason
 */
do_action('pb_booking_cancelled', $booking_id, $cancelled_by, $reason);

/**
 * Fired when deposit is paid
 *
 * @param int   $booking_id Booking ID
 * @param float $amount Deposit amount
 */
do_action('pb_deposit_paid', $booking_id, $amount);

/**
 * Fired when performer is paid out
 *
 * @param int   $booking_id Booking ID
 * @param int   $performer_id Performer ID
 * @param float $amount Payout amount
 */
do_action('pb_performer_paid', $booking_id, $performer_id, $amount);
```

### Marketplace Events

```php
/**
 * Fired when a marketplace event is posted
 *
 * @param int $event_id Event ID
 * @param int $customer_id Customer ID
 */
do_action('pb_event_posted', $event_id, $customer_id);

/**
 * Fired when a performer submits a bid
 *
 * @param int   $bid_id Bid ID
 * @param int   $event_id Event ID
 * @param int   $performer_id Performer ID
 * @param float $amount Bid amount
 */
do_action('pb_bid_submitted', $bid_id, $event_id, $performer_id, $amount);

/**
 * Fired when a bid is accepted
 *
 * @param int $bid_id Bid ID
 * @param int $event_id Event ID
 */
do_action('pb_bid_accepted', $bid_id, $event_id);
```

### Review System

```php
/**
 * Fired when a review is submitted
 *
 * @param int $review_id Review ID
 * @param int $booking_id Booking ID
 * @param int $rating Rating 1-5
 */
do_action('pb_review_submitted', $review_id, $booking_id, $rating);

/**
 * Fired when a review is flagged
 *
 * @param int    $review_id Review ID
 * @param int    $flagged_by User ID
 * @param string $reason Flag reason
 */
do_action('pb_review_flagged', $review_id, $flagged_by, $reason);
```

---

## Filter Hooks

### Performer Data

```php
/**
 * Filter performer data before save
 *
 * @param array $data Performer data
 * @param int   $performer_id Performer ID (0 for new)
 */
$data = apply_filters('pb_performer_data', $data, $performer_id);

/**
 * Filter performer display name
 *
 * @param string $name Display name
 * @param object $performer Performer object
 */
$name = apply_filters('pb_performer_display_name', $name, $performer);

/**
 * Filter performer search results
 *
 * @param array $performers Array of performers
 * @param array $args Search arguments
 */
$performers = apply_filters('pb_performer_search_results', $performers, $args);
```

### Booking Calculations

```php
/**
 * Filter booking total amount
 *
 * @param float $total Total amount
 * @param int   $performer_id Performer ID
 * @param array $booking_data Booking details
 */
$total = apply_filters('pb_booking_total', $total, $performer_id, $booking_data);

/**
 * Filter deposit percentage
 *
 * @param int    $percentage Deposit percentage
 * @param int    $performer_id Performer ID
 * @param float  $total Booking total
 */
$percentage = apply_filters('pb_deposit_percentage', $percentage, $performer_id, $total);

/**
 * Filter platform commission rate
 *
 * @param float $rate Commission rate (0.15 = 15%)
 * @param int   $performer_id Performer ID
 * @param float $amount Transaction amount
 */
$rate = apply_filters('pb_commission_rate', $rate, $performer_id, $amount);
```

### Availability

```php
/**
 * Filter available dates for a performer
 *
 * @param array $dates Array of date strings
 * @param int   $performer_id Performer ID
 * @param array $range Date range [start, end]
 */
$dates = apply_filters('pb_available_dates', $dates, $performer_id, $range);

/**
 * Filter time slots for a date
 *
 * @param array $slots Array of slots
 * @param int   $performer_id Performer ID
 * @param string $date Date string
 */
$slots = apply_filters('pb_available_slots', $slots, $performer_id, $date);
```

### Templates

```php
/**
 * Filter template file location
 *
 * @param string $template Template file path
 * @param string $slug Template slug
 * @param string $name Template name
 */
$template = apply_filters('pb_locate_template', $template, $slug, $name);

/**
 * Filter performer card HTML
 *
 * @param string $html HTML output
 * @param object $performer Performer object
 * @param array  $args Display arguments
 */
$html = apply_filters('pb_performer_card_html', $html, $performer, $args);
```

---

## REST API Extension

### Registering Custom Endpoints

```php
add_action('rest_api_init', function() {
    register_rest_route('peanut-booker/v1', '/custom/endpoint', [
        'methods'             => 'POST',
        'callback'            => 'my_custom_endpoint_handler',
        'permission_callback' => function($request) {
            return current_user_can('pb_performer');
        },
        'args' => [
            'param1' => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);
});

function my_custom_endpoint_handler($request) {
    $param1 = $request->get_param('param1');

    // Process request...

    return rest_ensure_response([
        'success' => true,
        'data'    => $result,
    ]);
}
```

### Extending Existing Endpoints

```php
// Add custom fields to performer response
add_filter('pb_rest_performer_response', function($response, $performer) {
    $response['custom_field'] = get_post_meta($performer->profile_id, '_custom_field', true);
    return $response;
}, 10, 2);

// Modify performer query parameters
add_filter('pb_rest_performers_query', function($args, $request) {
    if ($custom = $request->get_param('custom_filter')) {
        $args['meta_query'][] = [
            'key'   => '_custom_field',
            'value' => $custom,
        ];
    }
    return $args;
}, 10, 2);
```

---

## Custom Performer Fields

### Adding Meta Fields

```php
// Add field to performer edit screen
add_action('pb_performer_edit_fields', function($performer) {
    $value = pb_get_performer_meta($performer->id, 'custom_field');
    ?>
    <tr>
        <th>Custom Field</th>
        <td>
            <input type="text"
                   name="custom_field"
                   value="<?php echo esc_attr($value); ?>">
        </td>
    </tr>
    <?php
});

// Save the field
add_action('pb_performer_save', function($performer_id, $data) {
    if (isset($_POST['custom_field'])) {
        pb_update_performer_meta(
            $performer_id,
            'custom_field',
            sanitize_text_field($_POST['custom_field'])
        );
    }
}, 10, 2);
```

### Adding Profile Sections

```php
// Add section to public performer profile
add_action('pb_performer_profile_after_bio', function($performer) {
    $custom_data = pb_get_performer_meta($performer->id, 'custom_section_data');
    if ($custom_data) {
        echo '<div class="pb-custom-section">';
        echo '<h3>Custom Section</h3>';
        echo '<p>' . esc_html($custom_data) . '</p>';
        echo '</div>';
    }
});
```

---

## Custom Booking Statuses

```php
// Register custom status
add_filter('pb_booking_statuses', function($statuses) {
    $statuses['awaiting_documents'] = [
        'label'       => 'Awaiting Documents',
        'description' => 'Waiting for customer to submit documents',
        'color'       => '#f0ad4e',
    ];
    return $statuses;
});

// Add transition actions
add_action('pb_booking_status_changed', function($booking_id, $old_status, $new_status) {
    if ($new_status === 'awaiting_documents') {
        // Send document request email
        pb_send_document_request($booking_id);
    }
}, 10, 3);
```

---

## Custom Achievement Badges

```php
// Register custom badge
add_filter('pb_achievement_badges', function($badges) {
    $badges['festival_performer'] = [
        'name'        => 'Festival Performer',
        'description' => 'Performed at a Peanut Festival',
        'icon'        => 'dashicons-tickets',
        'points'      => 50,
    ];
    return $badges;
});

// Award badge programmatically
add_action('pf_performer_completed_show', function($performer_id) {
    $link = pf_get_booker_link($performer_id);
    if ($link) {
        pb_award_badge($link->booker_performer_id, 'festival_performer');
    }
});
```

---

## Email Templates

### Adding Custom Templates

```php
// Register template
add_filter('pb_email_templates', function($templates) {
    $templates['custom_notification'] = [
        'name'    => 'Custom Notification',
        'subject' => 'Important Update from {site_name}',
        'body'    => 'templates/emails/custom-notification.php',
    ];
    return $templates;
});

// Send custom email
pb_send_email('custom_notification', $recipient_email, [
    'performer_name' => $performer->name,
    'custom_data'    => $data,
]);
```

### Template Variables

```php
// Add custom variables to all templates
add_filter('pb_email_variables', function($vars, $template, $context) {
    $vars['custom_variable'] = get_option('my_custom_setting');
    return $vars;
}, 10, 3);
```

---

## WooCommerce Extensions

### Custom Product Types

```php
// Register booking product type
add_filter('product_type_selector', function($types) {
    $types['pb_booking'] = 'Booker Booking';
    return $types;
});

// Add product data tab
add_filter('woocommerce_product_data_tabs', function($tabs) {
    $tabs['pb_booking'] = [
        'label'  => 'Booking Settings',
        'target' => 'pb_booking_data',
    ];
    return $tabs;
});
```

### Order Processing

```php
// Hook into order completion
add_action('woocommerce_order_status_completed', function($order_id) {
    $order = wc_get_order($order_id);

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();

        if ($product->get_type() === 'pb_booking') {
            $booking_id = $item->get_meta('_pb_booking_id');
            pb_process_booking_payment($booking_id, $order_id);
        }
    }
});
```

---

## Shortcodes

### Registering Custom Shortcodes

```php
add_shortcode('pb_performer_list', function($atts) {
    $atts = shortcode_atts([
        'limit'    => 10,
        'category' => '',
        'orderby'  => 'rating',
    ], $atts);

    $performers = pb_get_performers([
        'limit'    => $atts['limit'],
        'category' => $atts['category'],
        'orderby'  => $atts['orderby'],
    ]);

    ob_start();
    include pb_locate_template('shortcodes/performer-list.php');
    return ob_get_clean();
});
```

---

## JavaScript Events

### Frontend Events

```javascript
// Listen for booking form submission
document.addEventListener('pb:booking:submit', function(e) {
    console.log('Booking submitted:', e.detail);
});

// Listen for availability calendar changes
document.addEventListener('pb:calendar:dateSelected', function(e) {
    console.log('Date selected:', e.detail.date);
});

// Listen for performer search
document.addEventListener('pb:search:complete', function(e) {
    console.log('Search results:', e.detail.performers);
});
```

### Triggering Events

```javascript
// Trigger custom event
const event = new CustomEvent('pb:custom:action', {
    detail: { data: 'value' }
});
document.dispatchEvent(event);
```

---

## Database Extension

### Adding Custom Tables

```php
register_activation_hook(__FILE__, function() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table = $wpdb->prefix . 'pb_custom_table';

    $sql = "CREATE TABLE $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        performer_id bigint(20) unsigned NOT NULL,
        custom_data text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY performer_id (performer_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});
```

---

## Security Considerations

### Capability Checks

Always check capabilities before performing actions:

```php
// Check performer can edit their own profile
if (!Peanut_Booker_Roles::can_edit_performer($performer, get_current_user_id())) {
    return new WP_Error('unauthorized', 'Cannot edit this profile', ['status' => 403]);
}

// Check booking access
if (!Peanut_Booker_Roles::can_view_booking($booking, get_current_user_id())) {
    return new WP_Error('unauthorized', 'Cannot view this booking', ['status' => 403]);
}
```

### Data Sanitization

```php
// Sanitize all input
$data = [
    'name'        => sanitize_text_field($input['name']),
    'bio'         => wp_kses_post($input['bio']),
    'email'       => sanitize_email($input['email']),
    'hourly_rate' => absint($input['hourly_rate']),
    'website'     => esc_url_raw($input['website']),
];
```

### Nonce Verification

```php
// In forms
wp_nonce_field('pb_custom_action', 'pb_nonce');

// In handlers
if (!wp_verify_nonce($_POST['pb_nonce'], 'pb_custom_action')) {
    wp_die('Security check failed');
}
```

---

## Testing

### Unit Test Example

```php
class Custom_Extension_Test extends WP_UnitTestCase {

    public function test_custom_booking_status() {
        // Create test booking
        $booking_id = pb_create_booking([
            'performer_id' => 1,
            'customer_id'  => 2,
            'event_date'   => date('Y-m-d', strtotime('+7 days')),
        ]);

        // Transition to custom status
        pb_update_booking_status($booking_id, 'awaiting_documents');

        // Assert email was sent
        $this->assertTrue(
            did_action('pb_document_request_sent') > 0
        );
    }
}
```

---

## Debugging

### Debug Mode

```php
// In wp-config.php
define('PB_DEBUG', true);

// Logs will appear in wp-content/debug.log
```

### Query Logging

```php
add_action('pb_query_executed', function($query, $time) {
    if (defined('PB_DEBUG') && PB_DEBUG) {
        error_log(sprintf(
            '[Peanut Booker] Query took %.4f seconds: %s',
            $time,
            $query
        ));
    }
}, 10, 2);
```
