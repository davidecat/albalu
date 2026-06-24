<?php
// phpcs:disable WordPress.Security.NonceVerification
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<tr id="custom_refresh_interval" style="display: none;">
    <td></td>
    <td>
        <div class="adt-custom-refresh-interval-container">
            <div class="adt-custom-refresh-interval-options-group adt-custom-refresh-interval-options-group-frequency">
                <label for="custom_refresh_interval_schedule"><?php esc_html_e( 'Frequency', 'woo-product-feed-elite' ); ?></label>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <input
                        type="radio"
                        class="custom_refresh_interval_schedule"
                        name="custom_refresh_interval_schedule"
                        value="hourly"
                        <?php checked( 'hourly', $data['schedule'] ); ?>
                    ><?php esc_html_e( 'Hourly', 'woo-product-feed-elite' ); ?>
                </div>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <input
                        type="radio"
                        class="custom_refresh_interval_schedule"
                        name="custom_refresh_interval_schedule"
                        value="daily"
                        <?php checked( 'daily', $data['schedule'] ); ?>
                    >
                    <?php esc_html_e( 'Daily', 'woo-product-feed-elite' ); ?>
                </div>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <input
                        type="radio"
                        class="custom_refresh_interval_schedule"
                        name="custom_refresh_interval_schedule"
                        value="twicedaily"
                        <?php checked( 'twicedaily', $data['schedule'] ); ?>
                    >
                    <?php esc_html_e( 'Twice Daily', 'woo-product-feed-elite' ); ?>
                </div>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <input
                        type="radio"
                        class="custom_refresh_interval_schedule"
                        name="custom_refresh_interval_schedule"
                        value="weekly"
                        <?php checked( 'weekly', $data['schedule'] ); ?>
                    >
                    <?php esc_html_e( 'Weekly', 'woo-product-feed-elite' ); ?>
                </div>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <input
                        type="radio"
                        class="custom_refresh_interval_schedule"
                        name="custom_refresh_interval_schedule"
                        value="monthly"
                        <?php checked( 'monthly', $data['schedule'] ); ?>
                    >
                    <?php esc_html_e( 'Monthly', 'woo-product-feed-elite' ); ?>
                </div>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <input
                        type="radio"
                        class="custom_refresh_interval_schedule"
                        name="custom_refresh_interval_schedule"
                        value="yearly"
                        <?php checked( 'yearly', $data['schedule'] ); ?>
                    >
                    <?php esc_html_e( 'Yearly', 'woo-product-feed-elite' ); ?>
                </div>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <span>
                        <input
                            type="radio"
                            class="custom_refresh_interval_schedule"
                            name="custom_refresh_interval_schedule"
                            value="hours"
                            <?php checked( 'hours', $data['schedule'] ); ?>
                        >
                        &nbsp;<?php esc_html_e( 'Every', 'woo-product-feed-elite' ); ?>
                    </span>
                    <input
                        type="number"
                        id="custom_refresh_interval_schedule_hours"
                        class="text sized custom_refresh_interval_schedule_hours"
                        name="custom_refresh_interval_schedule_hours"
                        size="2"
                        maxlength="2"
                        min="1"
                        max="24"
                        value="<?php echo esc_attr( $data['schedule_hours'] ); ?>"
                    >
                    <span><?php esc_html_e( 'hours', 'woo-product-feed-elite' ); ?></span>
                </div>
            </div>
            <div class="adt-custom-refresh-interval-options-group adt-custom-refresh-interval-options-group-days">
                <label for="custom_refresh_interval_days"><?php esc_html_e( 'Days', 'woo-product-feed-elite' ); ?></label>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <input
                        type="checkbox"
                        class="custom_refresh_interval_days"
                        name="custom_refresh_interval_days[]"
                        value="1"
                        <?php checked( in_array( 1, $data['days'], true ) ); ?>
                    >
                    <?php esc_html_e( 'Monday', 'woo-product-feed-elite' ); ?>
                </div>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <input
                        type="checkbox"
                        class="custom_refresh_interval_days"
                        name="custom_refresh_interval_days[]"
                        value="2"
                        <?php checked( in_array( 2, $data['days'], true ) ); ?>
                    >
                    <?php esc_html_e( 'Tuesday', 'woo-product-feed-elite' ); ?>
                </div>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <input
                        type="checkbox"
                        class="custom_refresh_interval_days"
                        name="custom_refresh_interval_days[]"
                        value="3"
                        <?php checked( in_array( 3, $data['days'], true ) ); ?>
                    >
                    <?php esc_html_e( 'Wednesday', 'woo-product-feed-elite' ); ?>
                </div>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <input
                        type="checkbox"
                        class="custom_refresh_interval_days"
                        name="custom_refresh_interval_days[]"
                        value="4"
                        <?php checked( in_array( 4, $data['days'], true ) ); ?>
                    >
                    <?php esc_html_e( 'Thursday', 'woo-product-feed-elite' ); ?>
                </div>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <input
                        type="checkbox"
                        class="custom_refresh_interval_days"
                        name="custom_refresh_interval_days[]"
                        value="5"
                        <?php checked( in_array( 5, $data['days'], true ) ); ?>
                    >
                    <?php esc_html_e( 'Friday', 'woo-product-feed-elite' ); ?>
                </div>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <input
                        type="checkbox"
                        class="custom_refresh_interval_days"
                        name="custom_refresh_interval_days[]"
                        value="6"
                        <?php checked( in_array( 6, $data['days'], true ) ); ?>
                    >
                    <?php esc_html_e( 'Saturday', 'woo-product-feed-elite' ); ?>
                </div>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <input
                        type="checkbox"
                        class="custom_refresh_interval_days"
                        name="custom_refresh_interval_days[]"
                        value="7"
                        <?php checked( in_array( 7, $data['days'], true ) ); ?>
                    >
                    <?php esc_html_e( 'Sunday', 'woo-product-feed-elite' ); ?>
                </div>
            </div>
            <div class="adt-custom-refresh-interval-options-group">
                <label for="custom_refresh_interval_commence"><?php esc_html_e( 'Commence', 'woo-product-feed-elite' ); ?></label>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <input
                        type="radio"
                        class="custom_refresh_interval_commence"
                        name="custom_refresh_interval_commence"
                        value="now"
                        <?php checked( 'now', $data['commence'] ); ?>
                    >
                    <?php esc_html_e( 'From now', 'woo-product-feed-elite' ); ?>
                </div>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <span>
                        <input
                            type="radio"
                            class="custom_refresh_interval_commence"
                            name="custom_refresh_interval_commence"
                            value="date"
                            <?php checked( 'date', $data['commence'] ); ?>
                        >
                        <?php esc_html_e( 'From', 'woo-product-feed-elite' ); ?>
                    </span>
                    <input
                        type="text"
                        id="custom_refresh_interval_commence_date"
                        class="sized custom_refresh_interval_commence_date"
                        name="custom_refresh_interval_commence_date"
                        size="20"
                        maxlength="20"
                        autocomplete="off"
                        value="<?php echo esc_attr( $data['commence_date'] ); ?>"
                    >
                </div>
                <div class="adt-custom-refresh-interval-options-group-item">
                    <span>
                        <?php esc_html_e( 'Local time is:', 'woo-product-feed-elite' ); ?>
                        <code><?php echo esc_html( wp_date( 'Y-m-d H:i:s' ) ); ?></code>
                    </span>
                </div>
            </div>
        </div>
    </td>
</tr>
