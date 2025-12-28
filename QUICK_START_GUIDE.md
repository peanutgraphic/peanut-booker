# Quick Start Guide - Peanut Booker v1.6.0 New Features

## Getting Started

### 1. Activate the New Features

After updating to this development branch:

```bash
# 1. Deactivate and reactivate the plugin to create new database tables
# Go to WordPress Admin > Plugins > Deactivate Peanut Booker
# Then reactivate it

# Or run via WP-CLI:
wp plugin deactivate peanut-booker
wp plugin activate peanut-booker
```

### 2. Verify Database Tables

Check that the new `wp_pb_messages` table was created:

```sql
SHOW TABLES LIKE 'wp_pb_messages';
```

---

## Using the Booking Wizard

### Replace Old Booking Form

In your theme or template file where you display the booking form:

**OLD:**
```php
// Old single-page form
include PEANUT_BOOKER_PATH . 'public/partials/booking-form.php';
```

**NEW:**
```php
// New 3-step wizard
echo Peanut_Booker_Booking_Wizard::render( $performer_id );
```

### Customize Wizard Appearance

Edit `/assets/css/booking-wizard.css` to match your theme:

```css
/* Change primary color */
.pb-btn-next,
.pb-btn-submit {
    background: #YOUR_COLOR;
}

/* Adjust step indicator colors */
.pb-wizard-step.active .pb-step-number {
    background: #YOUR_COLOR;
}
```

---

## Enable Instant Booking for Performers

### Add Toggle to Performer Dashboard

In your performer dashboard template:

```php
<?php
$performer = Peanut_Booker_Performer::get_by_user_id( get_current_user_id() );
$instant_enabled = Peanut_Booker_Performer::has_instant_booking( $performer->id );
?>

<div class="instant-booking-toggle">
    <label>
        <input type="checkbox"
               id="instant-booking-toggle"
               <?php checked( $instant_enabled ); ?>
               data-performer-id="<?php echo esc_attr( $performer->id ); ?>">
        Enable Instant Booking
    </label>
    <p class="description">
        Allow customers to book immediately without waiting for confirmation.
    </p>
</div>

<script>
jQuery('#instant-booking-toggle').on('change', function() {
    jQuery.post(ajaxurl, {
        action: 'pb_toggle_instant_booking',
        nonce: '<?php echo wp_create_nonce( 'pb_performer_nonce' ); ?>',
        enabled: this.checked
    }, function(response) {
        if (response.success) {
            alert(response.data.message);
        }
    });
});
</script>
```

### Display Instant Booking Badge

On performer profile pages:

```php
<?php if ( Peanut_Booker_Performer::has_instant_booking( $performer_id ) ) : ?>
    <span class="instant-booking-badge">
        Instant Booking Available
    </span>
<?php endif; ?>
```

---

## Add Messaging to Dashboards

### Customer Dashboard

Add to `/public/partials/dashboard-customer.php`:

```php
<div class="pb-dashboard-section">
    <h2>Messages</h2>
    <?php echo Peanut_Booker_Messages::render( get_current_user_id() ); ?>
</div>
```

### Performer Dashboard

Add to `/public/partials/dashboard-performer.php`:

```php
<div class="pb-dashboard-section">
    <h2>Messages</h2>
    <?php
    $unread = Peanut_Booker_Messages::get_unread_count( get_current_user_id() );
    if ( $unread > 0 ) {
        echo '<span class="unread-badge">' . $unread . ' unread</span>';
    }
    echo Peanut_Booker_Messages::render( get_current_user_id() );
    ?>
</div>
```

### Messaging JavaScript

Add to your custom JavaScript file:

```javascript
// Auto-refresh messages every 30 seconds
setInterval(function() {
    loadConversations();
}, 30000);

function loadConversations() {
    jQuery.get(ajaxurl, {
        action: 'pb_get_conversations',
        nonce: pbMessagesNonce
    }, function(response) {
        if (response.success) {
            updateConversationsList(response.data.conversations);
            updateUnreadCount(response.data.unread_total);
        }
    });
}
```

---

## Display Performer Metrics

### On Performer Profile

Add to `/templates/single-performer.php`:

```php
<?php
$metrics = Peanut_Booker_Performer::get_metrics( $performer_id );
?>

<div class="performer-metrics">
    <h3>Performance Stats</h3>

    <div class="metric">
        <span class="metric-label">Response Time:</span>
        <span class="metric-value">
            <?php echo esc_html( $metrics['response_time_hours'] ); ?> hours
        </span>
    </div>

    <div class="metric">
        <span class="metric-label">Acceptance Rate:</span>
        <span class="metric-value">
            <?php echo esc_html( $metrics['acceptance_rate'] ); ?>%
        </span>
    </div>

    <div class="metric">
        <span class="metric-label">Completed Bookings:</span>
        <span class="metric-value">
            <?php echo esc_html( $metrics['completed_bookings'] ); ?>
        </span>
    </div>
</div>
```

### Style the Metrics

```css
.performer-metrics {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.metric {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #e0e0e0;
}

.metric:last-child {
    border-bottom: none;
}

.metric-value {
    font-weight: bold;
    color: #2196F3;
}
```

---

## Track Response Times

### Automatically Track on Booking Confirmation

Edit `/includes/class-booking.php`, find the `performer_confirm` method and add:

```php
public static function performer_confirm( $booking_id, $performer_user_id ) {
    // ... existing code ...

    // Track response time
    $performer = Peanut_Booker_Performer::get_by_user_id( $performer_user_id );
    if ( $performer ) {
        Peanut_Booker_Performer::track_response_time( $performer->id, $booking_id );
    }

    // ... rest of method ...
}
```

---

## Common Customizations

### Change Wizard Step Names

Edit `/templates/booking-wizard.php`:

```php
<div class="pb-wizard-step" data-step="1">
    <span class="pb-step-number">1</span>
    <span class="pb-step-label">Your Custom Step Name</span>
</div>
```

### Add Custom Validation

In `/includes/class-booking-wizard.php`, add to validation methods:

```php
private function validate_step_2( $performer_id, $data ) {
    // ... existing validation ...

    // Add custom validation
    if ( $data['event_date'] === '2024-12-25' ) {
        return new WP_Error( 'holiday', __( 'Christmas bookings require special approval', 'peanut-booker' ) );
    }

    // ... rest of method ...
}
```

### Custom Message Templates

Create message templates for common scenarios:

```php
// In your custom code
function send_booking_inquiry( $booking_id, $performer_id, $customer_id ) {
    $booking = Peanut_Booker_Booking::get( $booking_id );

    $message = sprintf(
        "Hi! I'm interested in booking you for %s on %s. Can you confirm availability?",
        $booking->event_title,
        date( 'F j, Y', strtotime( $booking->event_date ) )
    );

    Peanut_Booker_Messages::send( $customer_id, $performer_id, $message, $booking_id );
}
```

---

## Troubleshooting

### Wizard Not Showing

**Problem:** Wizard appears blank or doesn't load.

**Solution:**
1. Check browser console for JavaScript errors
2. Verify scripts are enqueued: `View Source` → Look for `booking-wizard.js`
3. Clear browser cache
4. Check file permissions on `/assets/js/booking-wizard.js`

### Messages Not Sending

**Problem:** Messages don't appear or fail silently.

**Solution:**
1. Verify database table exists: `SELECT * FROM wp_pb_messages LIMIT 1;`
2. Check AJAX nonce is valid
3. Look in PHP error logs for database errors
4. Verify users exist and have correct IDs

### Metrics Not Calculating

**Problem:** Metrics show 0 or incorrect values.

**Solution:**
1. Ensure bookings have `performer_confirmed = 1`
2. Check `created_at` and `updated_at` timestamps are set
3. Verify booking statuses are correct
4. Run: `SELECT COUNT(*) FROM wp_pb_bookings WHERE performer_id = X`

### Instant Booking Not Working

**Problem:** Toggle doesn't save or instant booking doesn't bypass confirmation.

**Solution:**
1. Check post meta: `SELECT * FROM wp_postmeta WHERE meta_key = 'pb_instant_booking_enabled'`
2. Verify performer profile ID is correct
3. Check availability calendar has no conflicts
4. Look for JavaScript console errors

---

## Performance Tips

### Cache Metrics

```php
// Cache metrics for 1 hour
$cache_key = 'pb_metrics_' . $performer_id;
$metrics = get_transient( $cache_key );

if ( false === $metrics ) {
    $metrics = Peanut_Booker_Performer::get_metrics( $performer_id );
    set_transient( $cache_key, $metrics, HOUR_IN_SECONDS );
}
```

### Limit Message History

```php
// Load only last 50 messages
$messages = Peanut_Booker_Messages::get_conversation( $user1_id, $user2_id, 50 );
```

### Paginate Conversations

```php
$page = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;
$per_page = 10;
$offset = ( $page - 1 ) * $per_page;

$conversations = Peanut_Booker_Messages::get_conversations( $user_id );
$paged_convos = array_slice( $conversations, $offset, $per_page );
```

---

## Security Best Practices

### Always Verify Permissions

```php
// Before sending message
if ( ! current_user_can( 'pb_send_messages' ) ) {
    wp_die( 'Unauthorized' );
}

// Before viewing metrics
$performer = Peanut_Booker_Performer::get_by_user_id( get_current_user_id() );
if ( ! $performer || $performer->id !== $performer_id ) {
    wp_die( 'Unauthorized' );
}
```

### Sanitize All Inputs

```php
$message = sanitize_textarea_field( $_POST['message'] );
$recipient_id = absint( $_POST['recipient_id'] );
```

### Escape All Outputs

```php
echo esc_html( $message->message );
echo esc_attr( $performer->id );
echo esc_url( $avatar_url );
```

---

## Testing Your Implementation

### Test Wizard Flow

1. Visit a performer profile
2. Click "Book Now"
3. Complete Step 1 (select service)
4. Complete Step 2 (pick date/time)
5. Review Step 3
6. Submit booking
7. Verify booking created in database

### Test Messaging

1. Log in as customer
2. Send message to performer
3. Log out and log in as performer
4. Verify message appears with unread badge
5. Reply to message
6. Check email notification sent
7. Verify conversation thread displays correctly

### Test Metrics

1. Create several test bookings
2. Confirm some, leave some pending
3. View performer profile
4. Verify metrics display:
   - Response time calculates
   - Acceptance rate shows percentage
   - Completed bookings count correct

---

## Support Resources

- **Documentation:** See `IMPLEMENTATION_SUMMARY.md`
- **WordPress Codex:** https://codex.wordpress.org/
- **WooCommerce Docs:** https://woocommerce.com/documentation/
- **WCAG Guidelines:** https://www.w3.org/WAI/WCAG21/quickref/
- **Code Standards:** https://developer.wordpress.org/coding-standards/

---

## Next Steps

1. Test all features in staging environment
2. Customize styling to match your theme
3. Add custom validation rules as needed
4. Train users on new features
5. Monitor performance and optimize
6. Collect user feedback
7. Plan for production deployment

---

**Happy Coding!**
