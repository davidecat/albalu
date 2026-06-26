<?php
// phpcs:disable WordPress.Security.NonceVerification
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AdTribes\PFE\Helpers\Product_Feed_Helper;
use AdTribes\PFE\Factories\Admin_Notice;

$history_products_chart_data = '';
$feed_id                     = sanitize_text_field( $_GET['id'] );
if ( $feed_id ) {
    $feed = Product_Feed_Helper::get_product_feed( $feed_id );
    if ( $feed ) {
        $history_products_chart_data = Product_Feed_Helper::get_history_products_chart_data( $feed );
    }
}
?>
<div class="wrap">
    <div class="woo-product-feed-pro-form-style-2">
        <div class="woo-product-feed-pro-form-style-2-heading">
            <a href="https://adtribes.io/?utm_source=pfe&utm_medium=logo&utm_campaign=adminpagelogo" target="_blank"><img src="<?php echo esc_url( ADT_PFE_IMAGES_URL . 'adt-logo.png' ); ?>" alt="AdTribes"></a>
            <h1 class="title"><?php esc_html_e( 'Feed statistics', 'woo-product-feed-elite' ); ?></h1>
        </div>

        <?php
        // Display info message notice.
        $admin_notice = new Admin_Notice(
            __( 'The graph shows the amount of products in this product feed, measured after every scheduled and/or manually triggered refresh', 'woo-product-feed-elite' ),
            'info',
        );
        $admin_notice->run();
        ?>

        <input type="hidden" id="history_products_chart_data" name="history_products_chart_data" value="<?php echo esc_attr( $history_products_chart_data ); ?>">
        <div id="content">
            <div class="chart-container">
                <div id="placeholder" class="chart-placeholder main" style="width:auto;height:400px;"></div>
            </div>
        </div>
    </div>
</div>
