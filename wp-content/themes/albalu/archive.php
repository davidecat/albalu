<?php
/**
 * Blog listing - 3-column grid layout
 * Matches production: https://www.albalu.it/notizie/
 */
defined( 'ABSPATH' ) || exit;

get_header();

$posts_page_id = get_option( 'page_for_posts' );
if ( is_home() && $posts_page_id ) {
    $page_title   = get_the_title( $posts_page_id );
    $page_content = get_post_field( 'post_content', $posts_page_id );
    $subtitle     = $page_content ? wp_strip_all_tags( $page_content ) : __( "Da leggere tutto d'un fiato… Scopri le ultime tendenze e novità nel mondo delle celebrazioni tradizionali cristiane.", 'albalu' );
} else {
    $page_title = null;
    $subtitle   = null;
}
?>

<div id="content" class="site-content p-0 bg-albalu-warm">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">

            <section class="blog-listing-header py-5">
                <div class="container">
                    <p class="text-uppercase fw-bold fs-6 letter-spacing-2 mb-2 text-muted">Albalù store</p>
                    <?php if ( $page_title ) : ?>
                        <h1 class="fw-bold mb-3"><?php echo wp_kses_post( $page_title ); ?></h1>
                        <p class="lead text-muted mb-0"><?php echo esc_html( $subtitle ); ?></p>
                    <?php else : ?>
                        <?php the_archive_title( '<h1 class="fw-bold mb-3">', '</h1>' ); ?>
                        <?php the_archive_description( '<p class="lead text-muted mb-0">', '</p>' ); ?>
                    <?php endif; ?>
                </div>
            </section>

            <section class="blog-listing-posts py-4 pb-5">
                <div class="container">
                    <?php if ( have_posts() ) : ?>
                        <div class="row g-4 blog-posts-grid">
                            <?php while ( have_posts() ) : the_post(); ?>
                                <article class="col-md-6 col-lg-4 blog-post-card">
                                    <div class="card h-100 border-0 shadow-sm bg-white overflow-hidden">
                                        <?php if ( has_post_thumbnail() ) : ?>
                                            <a href="<?php the_permalink(); ?>" class="d-block ratio ratio-16x10 overflow-hidden">
                                                <?php the_post_thumbnail( 'medium_large', array( 'class' => 'card-img-top object-fit-cover' ) ); ?>
                                            </a>
                                        <?php endif; ?>
                                        <div class="card-body text-start">
                                            <h3 class="h6 fw-bold mb-2">
                                                <a href="<?php the_permalink(); ?>" class="text-body text-decoration-none"><?php the_title(); ?></a>
                                            </h3>
                                            <p class="card-text text-muted small mb-2"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 25 ) ); ?></p>
                                            <a href="<?php the_permalink(); ?>" class="text-primary text-decoration-none fw-medium small"><?php esc_html_e( 'Leggi Tutto', 'albalu' ); ?> &rarr;</a>
                                            <p class="small text-muted mt-2 mb-0"><?php echo esc_html( date_i18n( 'j F Y', get_the_date( 'U' ) ) ); ?></p>
                                        </div>
                                    </div>
                                </article>
                            <?php endwhile; ?>
                        </div>

                        <nav class="blog-pagination mt-5" aria-label="<?php esc_attr_e( 'Paginazione articoli', 'albalu' ); ?>">
                            <?php
                            if ( function_exists( 'bootscore_pagination' ) ) {
                                bootscore_pagination();
                            } else {
                                the_posts_pagination( array(
                                    'mid_size'  => 2,
                                    'prev_text' => '&laquo;',
                                    'next_text' => '&raquo;',
                                ) );
                            }
                            ?>
                        </nav>
                    <?php else : ?>
                        <p class="py-5 text-center"><?php esc_html_e( 'Nessun articolo trovato.', 'albalu' ); ?></p>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>
</div>

<?php
get_footer();
