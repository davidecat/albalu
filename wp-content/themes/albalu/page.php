<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>

<div id="content" class="site-content p-0">

    <?php if ( ! is_front_page() ) : ?>
    <section class="page-title-bar bg-albalu-warm py-4 mb-4">
        <div class="container">
            <?php
            if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
                echo '<h1 class="fs-2 fw-normal mb-0">' . esc_html__( 'Ordine ricevuto', 'albalu' ) . '</h1>';
            } else {
                the_title('<h1 class="fs-2 fw-normal mb-0">', '</h1>');
            }
            ?>
        </div>
    </section>
    <?php endif; ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <?php the_post(); ?>
            <div class="container pt-3 pb-5">
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
get_footer();
