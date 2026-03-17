<?php
/**
 * The template for displaying search forms
 *
 * @package Bootscore
 */

defined( 'ABSPATH' ) || exit;
?>

<form role="search" method="get" class="search-form position-relative" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <div class="input-group">
        <input type="search" class="form-control rounded-pill border-end-0 bg-light ps-4 py-2" 
               placeholder="<?php echo esc_attr_x( 'Cerca...', 'placeholder', 'bootscore' ); ?>" 
               value="<?php echo get_search_query(); ?>" name="s" 
               style="border-color: #EAE3E0;" />
        <button type="submit" class="btn btn-outline-secondary rounded-pill border-start-0 bg-light pe-4 py-2" style="border-color: #EAE3E0;">
            <i class="fas fa-search text-muted"></i>
        </button>
    </div>
</form>
