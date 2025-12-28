<?php
/**
 * Admin performers list template.
 *
 * @package Peanut_Booker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}
?>

<div class="wrap pb-admin-performers">
    <h1>
        <?php esc_html_e( 'Performers', 'peanut-booker' ); ?>
    </h1>

    <div class="pb-admin-filters">
        <select id="pb-filter-tier">
            <option value=""><?php esc_html_e( 'All Tiers', 'peanut-booker' ); ?></option>
            <option value="free"><?php esc_html_e( 'Free', 'peanut-booker' ); ?></option>
            <option value="pro"><?php esc_html_e( 'Pro', 'peanut-booker' ); ?></option>
        </select>
        <select id="pb-filter-verified">
            <option value=""><?php esc_html_e( 'All Verification', 'peanut-booker' ); ?></option>
            <option value="1"><?php esc_html_e( 'Verified', 'peanut-booker' ); ?></option>
            <option value="0"><?php esc_html_e( 'Not Verified', 'peanut-booker' ); ?></option>
        </select>
        <input type="text" id="pb-search-performers" placeholder="<?php esc_attr_e( 'Search performers...', 'peanut-booker' ); ?>">
    </div>

    <?php if ( empty( $performers ) ) : ?>
        <div class="pb-empty-state">
            <h3><?php esc_html_e( 'No performers yet', 'peanut-booker' ); ?></h3>
            <p><?php esc_html_e( 'Performers will appear here once they sign up.', 'peanut-booker' ); ?></p>
        </div>
    <?php else : ?>
        <table class="pb-admin-table widefat">
            <thead>
                <tr>
                    <th class="check-column"><input type="checkbox" id="pb-select-all"></th>
                    <th><?php esc_html_e( 'Performer', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Tier', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Level', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Bookings', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Rating', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Revenue', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'peanut-booker' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'peanut-booker' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $performers as $performer ) : ?>
                    <?php
                    $user           = get_userdata( $performer->user_id );
                    $profile_id     = $performer->profile_id;
                    $level          = $performer->achievement_level ?: 'bronze';
                    $profile_url    = $profile_id ? get_edit_post_link( $profile_id ) : '#';
                    $total_revenue  = Peanut_Booker_Performer::get_total_earnings( $performer->id );
                    $is_active      = ( 'active' === $performer->status );
                    ?>
                    <tr>
                        <td><input type="checkbox" class="pb-item-checkbox" value="<?php echo esc_attr( $performer->id ); ?>"></td>
                        <td>
                            <div class="pb-performer-info">
                                <?php echo get_avatar( $performer->user_id, 40 ); ?>
                                <div>
                                    <strong><?php echo $user ? esc_html( $user->display_name ) : '—'; ?></strong>
                                    <?php if ( $performer->is_verified ) : ?>
                                        <span class="pb-verified-badge" title="<?php esc_attr_e( 'Verified', 'peanut-booker' ); ?>">✓</span>
                                    <?php endif; ?>
                                    <br>
                                    <small><?php echo $user ? esc_html( $user->user_email ) : ''; ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="pb-tier pb-tier-<?php echo esc_attr( $performer->tier ); ?>">
                                <?php echo esc_html( ucfirst( $performer->tier ) ); ?>
                            </span>
                        </td>
                        <td>
                            <span class="pb-level pb-level-<?php echo esc_attr( $level ); ?>">
                                <?php echo esc_html( ucfirst( $level ) ); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html( $performer->completed_bookings ); ?></td>
                        <td>
                            <?php if ( $performer->average_rating ) : ?>
                                <?php echo esc_html( number_format( $performer->average_rating, 1 ) ); ?> ★
                                <small>(<?php echo esc_html( $performer->total_reviews ); ?>)</small>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?php echo wc_price( $total_revenue ); ?></td>
                        <td>
                            <span class="pb-status pb-status-<?php echo $is_active ? 'active' : 'inactive'; ?>">
                                <?php echo $is_active ? esc_html__( 'Active', 'peanut-booker' ) : esc_html__( 'Inactive', 'peanut-booker' ); ?>
                            </span>
                        </td>
                        <td>
                            <div class="pb-row-actions">
                                <?php if ( $profile_id ) : ?>
                                    <a href="<?php echo esc_url( get_permalink( $profile_id ) ); ?>" target="_blank">
                                        <?php esc_html_e( 'View', 'peanut-booker' ); ?>
                                    </a> |
                                    <a href="<?php echo esc_url( $profile_url ); ?>">
                                        <?php esc_html_e( 'Edit', 'peanut-booker' ); ?>
                                    </a> |
                                <?php endif; ?>
                                <?php if ( ! $performer->is_verified ) : ?>
                                    <button class="pb-verify-performer button-link" data-performer-id="<?php echo esc_attr( $performer->id ); ?>" data-verify="1">
                                        <?php esc_html_e( 'Verify', 'peanut-booker' ); ?>
                                    </button>
                                <?php else : ?>
                                    <button class="pb-verify-performer button-link" data-performer-id="<?php echo esc_attr( $performer->id ); ?>" data-verify="0">
                                        <?php esc_html_e( 'Unverify', 'peanut-booker' ); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pb-bulk-actions">
            <select id="pb-bulk-action-select">
                <option value=""><?php esc_html_e( 'Bulk Actions', 'peanut-booker' ); ?></option>
                <option value="verify"><?php esc_html_e( 'Verify Selected', 'peanut-booker' ); ?></option>
                <option value="unverify"><?php esc_html_e( 'Remove Verification', 'peanut-booker' ); ?></option>
                <option value="activate"><?php esc_html_e( 'Activate', 'peanut-booker' ); ?></option>
                <option value="deactivate"><?php esc_html_e( 'Deactivate', 'peanut-booker' ); ?></option>
            </select>
            <button class="button pb-bulk-action"><?php esc_html_e( 'Apply', 'peanut-booker' ); ?></button>
        </div>
    <?php endif; ?>
</div>
