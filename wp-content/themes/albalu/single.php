<?php
/**
 * Single post - blog detail layout
 * Matches production design with hero section
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>

<div id="content" class="site-content p-0 bg-albalu-warm">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">

            <?php while ( have_posts() ) : the_post(); ?>

                <section class="single-post-hero py-5">
                    <div class="container">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-6">
                                <p class="text-uppercase fw-bold fs-6 letter-spacing-2 mb-2 text-muted"><?php esc_html_e( 'Blog Albalù', 'albalu' ); ?></p>
                                <h1 class="fw-bold mb-3"><?php the_title(); ?></h1>
                                <a href="#entry-content" class="text-body text-decoration-none d-inline-flex align-items-center gap-1">
                                    <?php esc_html_e( 'Leggi tutto', 'albalu' ); ?> <i class="fas fa-chevron-down small"></i>
                                </a>
                            </div>
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="col-lg-6">
                                    <div class="ratio ratio-4x3 overflow-hidden rounded">
                                        <?php the_post_thumbnail( 'large', array( 'class' => 'object-fit-cover' ) ); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <section id="entry-content" class="single-post-content py-5">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="bg-white rounded shadow-sm p-4 p-md-5 entry-content chi-section-content">
                                    <?php the_content(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <?php
                if ( function_exists( 'have_rows' ) && have_rows( 'chi_siamo_sections', get_the_ID() ) ) :
                    get_template_part( 'template-parts/blog-sections', null, array( 'blog_sections_post_id' => get_the_ID() ) );
                endif;
                ?>

            <?php endwhile; ?>

        </main>
    </div>
</div>

<?php
get_footer();
