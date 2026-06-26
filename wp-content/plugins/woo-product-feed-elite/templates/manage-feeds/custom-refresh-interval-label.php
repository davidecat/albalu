<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AdTribes\PFE\Helpers\Formatting;
?>
<div class="adt-tw-flex adt-tw-items-center adt-tw-gap-1">
    <span class="adt-tooltip">
        <span class="adt-tw-icon-[lucide--calendar-clock] adt-tw-w-4 adt-tw-h-4 adt-tw-text-gray-400"></span>
        <div class="adt-tooltip-content">
            <p>
                <?php esc_html_e( 'Schedule:', 'woo-product-feed-elite' ); ?><br>
                <strong><?php echo esc_html( Formatting::format_custom_refresh_interval_schedule( $custom_interval['schedule'] ?? '', $custom_interval['schedule_hours'] ?? '' ) ); ?></strong>
            </p>
            <?php if ( isset( $custom_interval['schedule'] ) && in_array( $custom_interval['schedule'], array( 'hourly', 'hours', 'daily', 'twicedaily' ), true ) && ! empty( $custom_interval['days'] ) ) : ?>
                <p>
                    <?php esc_html_e( 'Days:', 'woo-product-feed-elite' ); ?><br>
                    <strong><?php echo esc_html( Formatting::format_custom_refresh_interval_days( $custom_interval['days'] ?? array() ) ); ?></strong>
                </p>
            <?php endif; ?>
            <?php if ( 'date' === $custom_interval['commence'] && ! empty( $custom_interval['commence_date'] ) ) : ?>
            <p>
                <?php esc_html_e( 'Commence:', 'woo-product-feed-elite' ); ?><br>
                <strong><?php echo esc_html( Formatting::format_date( $custom_interval['commence_date'] ) ); ?></strong>
            </p>
            <?php endif; ?>
        </div>
    </span>
    <?php esc_html_e( 'Custom', 'woo-product-feed-elite' ); ?>
</div>
