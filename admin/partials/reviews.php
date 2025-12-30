<?php
/**
 * Admin reviews list template.
 *
 * @package Peanut_Booker
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'all';
?>

<div class="wrap pb-admin-reviews">
    <h1><?php esc_html_e( 'Reviews', 'peanut-booker' ); ?></h1>

    <div class="pb-settings-tabs">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-reviews' ) ); ?>" class="<?php echo 'all' === $current_tab ? 'active' : ''; ?>">
            <?php esc_html_e( 'All Reviews', 'peanut-booker' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=pb-reviews&tab=flagged' ) ); ?>" class="<?php echo 'flagged' === $current_tab ? 'active' : ''; ?>">
            <?php esc_html_e( 'Flagged for Arbitration', 'peanut-booker' ); ?>
            <?php
            $flagged_count = count( Peanut_Booker_Reviews::get_pending_arbitration() );
            if ( $flagged_count > 0 ) :
            ?>
                <span class="pb-badge"><?php echo esc_html( $flagged_count ); ?></span>
            <?php endif; ?>
        </a>
    </div>

    <?php if ( empty( $reviews ) ) : ?>
        <div class="pb-empty-state">
            <h3>
                <?php
                if ( 'flagged' === $current_tab ) {
                    esc_html_e( 'No reviews awaiting arbitration', 'peanut-booker' );
                } else {
                    esc_html_e( 'No reviews yet', 'peanut-booker' );
                }
                ?>
            </h3>
            <p>
                <?php
                if ( 'flagged' === $current_tab ) {
                    esc_html_e( 'Great! There are no flagged reviews that need your attention.', 'peanut-booker' );
                } else {
                    esc_html_e( 'Reviews will appear here once customers and performers leave feedback.', 'peanut-booker' );
                }
                ?>
            </p>
        </div>
    <?php else : ?>

        <?php if ( 'flagged' === $current_tab ) : ?>
            <!-- Flagged Reviews with Arbitration Forms -->
            <div class="pb-flagged-reviews">
                <?php foreach ( $reviews as $review ) : ?>
                    <div class="pb-review-item pb-settings-section">
                        <div class="pb-review-header">
                            <div class="pb-review-parties">
                                <strong><?php esc_html_e( 'From:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $review['reviewer_name'] ); ?>
                                <br>
                                <strong><?php esc_html_e( 'About:', 'peanut-booker' ); ?></strong> <?php echo esc_html( $review['reviewee_name'] ); ?>
                            </div>
                            <div class="pb-review-meta">
                                <span class="pb-review-rating">
                                    <?php echo esc_html( $review['rating'] ); ?> ★
                                </span>
                                <span class="pb-review-date">
                                    <?php echo esc_html( $review['date_formatted'] ); ?>
                                </span>
                            </div>
                        </div>

                        <div class="pb-review-detail">
                            <?php if ( $review['title'] ) : ?>
                                <h4><?php echo esc_html( $review['title'] ); ?></h4>
                            <?php endif; ?>
                            <blockquote><?php echo esc_html( $review['content'] ); ?></blockquote>
                        </div>

                        <?php if ( $review['response'] ) : ?>
                            <div class="pb-review-response-display">
                                <strong><?php esc_html_e( 'Response:', 'peanut-booker' ); ?></strong>
                                <p><?php echo esc_html( $review['response'] ); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="pb-flag-reason">
                            <strong><?php esc_html_e( 'Flag Reason:', 'peanut-booker' ); ?></strong>
                            <p><?php echo esc_html( $review['flag_reason'] ?: __( 'No reason provided.', 'peanut-booker' ) ); ?></p>
                            <small><?php esc_html_e( 'Flagged by:', 'peanut-booker' ); ?> <?php echo esc_html( $review['flagged_by_name'] ?? __( 'Unknown', 'peanut-booker' ) ); ?></small>
                        </div>

                        <form class="pb-arbitration-form">
                            <input type="hidden" name="review_id" value="<?php echo esc_attr( $review['id'] ); ?>">

                            <div class="pb-form-row">
                                <label for="pb-decision-<?php echo esc_attr( $review['id'] ); ?>">
                                    <?php esc_html_e( 'Decision:', 'peanut-booker' ); ?>
                                </label>
                                <select name="decision" id="pb-decision-<?php echo esc_attr( $review['id'] ); ?>" required>
                                    <option value=""><?php esc_html_e( 'Select action...', 'peanut-booker' ); ?></option>
                                    <option value="keep"><?php esc_html_e( 'Keep Review (Dismiss Flag)', 'peanut-booker' ); ?></option>
                                    <option value="hide"><?php esc_html_e( 'Hide Review (Keep in Database)', 'peanut-booker' ); ?></option>
                                    <option value="remove"><?php esc_html_e( 'Remove Review Permanently', 'peanut-booker' ); ?></option>
                                </select>
                            </div>

                            <div class="pb-form-row">
                                <label for="pb-notes-<?php echo esc_attr( $review['id'] ); ?>">
                                    <?php esc_html_e( 'Admin Notes (sent to both parties):', 'peanut-booker' ); ?>
                                </label>
                                <textarea name="admin_notes" id="pb-notes-<?php echo esc_attr( $review['id'] ); ?>" rows="3" placeholder="<?php esc_attr_e( 'Explain your decision...', 'peanut-booker' ); ?>"></textarea>
                            </div>

                            <button type="submit" class="button button-primary">
                                <?php esc_html_e( 'Submit Decision', 'peanut-booker' ); ?>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else : ?>
            <!-- All Reviews Table -->
            <table class="pb-admin-table widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'ID', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Type', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'From', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'About', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Rating', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Review', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Date', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'peanut-booker' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'peanut-booker' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $reviews as $review ) : ?>
                        <tr>
                            <td>#<?php echo esc_html( $review['id'] ); ?></td>
                            <td>
                                <?php
                                if ( 'performer' === $review['review_type'] ) {
                                    esc_html_e( 'For Performer', 'peanut-booker' );
                                } else {
                                    esc_html_e( 'For Customer', 'peanut-booker' );
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html( $review['reviewer_name'] ); ?></td>
                            <td><?php echo esc_html( $review['reviewee_name'] ); ?></td>
                            <td>
                                <span class="pb-star-display">
                                    <?php echo esc_html( $review['rating'] ); ?> ★
                                </span>
                            </td>
                            <td>
                                <?php if ( $review['title'] ) : ?>
                                    <strong><?php echo esc_html( $review['title'] ); ?></strong><br>
                                <?php endif; ?>
                                <?php echo esc_html( wp_trim_words( $review['content'], 15 ) ); ?>
                            </td>
                            <td><?php echo esc_html( $review['date_formatted'] ); ?></td>
                            <td>
                                <?php if ( $review['is_flagged'] ) : ?>
                                    <span class="pb-status pb-status-pending"><?php esc_html_e( 'Flagged', 'peanut-booker' ); ?></span>
                                <?php elseif ( $review['is_hidden'] ) : ?>
                                    <span class="pb-status pb-status-cancelled"><?php esc_html_e( 'Hidden', 'peanut-booker' ); ?></span>
                                <?php else : ?>
                                    <span class="pb-status pb-status-active"><?php esc_html_e( 'Visible', 'peanut-booker' ); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="pb-row-actions">
                                    <a href="#" class="pb-toggle-details" data-target="#pb-review-<?php echo esc_attr( $review['id'] ); ?>">
                                        <?php esc_html_e( 'View', 'peanut-booker' ); ?>
                                    </a>
                                    <?php if ( ! $review['is_hidden'] ) : ?>
                                        | <a href="#" class="pb-hide-review" data-review-id="<?php echo esc_attr( $review['id'] ); ?>">
                                            <?php esc_html_e( 'Hide', 'peanut-booker' ); ?>
                                        </a>
                                    <?php else : ?>
                                        | <a href="#" class="pb-show-review" data-review-id="<?php echo esc_attr( $review['id'] ); ?>">
                                            <?php esc_html_e( 'Show', 'peanut-booker' ); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <tr id="pb-review-<?php echo esc_attr( $review['id'] ); ?>" style="display: none;">
                            <td colspan="9">
                                <div class="pb-review-detail">
                                    <?php if ( $review['title'] ) : ?>
                                        <h4><?php echo esc_html( $review['title'] ); ?></h4>
                                    <?php endif; ?>
                                    <blockquote><?php echo esc_html( $review['content'] ); ?></blockquote>
                                    <?php if ( $review['response'] ) : ?>
                                        <div class="pb-review-response-display">
                                            <strong><?php esc_html_e( 'Response:', 'peanut-booker' ); ?></strong>
                                            <p><?php echo esc_html( $review['response'] ); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    <?php endif; ?>
</div>
