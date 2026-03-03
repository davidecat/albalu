<?php
/* Template Name: Albalu Default */

get_header();
?>

<div id="content" class="site-content">
    
    <!-- Full Width Header Section -->
    <header class="entry-header bg-light py-5 mb-5 border-bottom">
        <div class="container">
            <?php the_title('<h1 class="entry-title m-0 display-4 fw-bold text-dark">', '</h1>'); ?>
            
            <!-- Breadcrumb -->
            <div class="mt-3 small text-muted">
            <?php 
            if ( function_exists('yoast_breadcrumb') ) {
                yoast_breadcrumb( '<p id="breadcrumbs" class="mb-0">','</p>' );
            } elseif ( function_exists('woocommerce_breadcrumb') ) {
                woocommerce_breadcrumb();
            }
            ?>
            </div>
        </div>
    </header>

    <div id="primary" class="content-area container pb-5">
        <main id="main" class="site-main">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="entry-content">
                        <?php
                        while ( have_posts() ) :
                            the_post();
                            the_content();
                        endwhile;
                        ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
get_footer();
