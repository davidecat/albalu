<?php
/**
 * The template for displaying search forms
 *
 * @package Bootscore
 */

defined( 'ABSPATH' ) || exit;

$albalu_current_event = isset( $_GET['event'] ) ? sanitize_text_field( wp_unslash( $_GET['event'] ) ) : '';
$albalu_events        = function_exists( 'albalu_get_search_events' ) ? albalu_get_search_events() : array();
?>

<form role="search" method="get" class="search-form position-relative" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <?php if ( ! empty( $albalu_events ) ) : ?>
        <label class="small fw-medium mb-1 d-block">Cerca in:</label>
        <div class="d-flex gap-2 align-items-stretch">
            <select name="event" class="form-select form-select-sm rounded-pill bg-light" style="max-width: 130px; border-color: #EAE3E0; box-shadow: none;">
                <option value="">Tutti</option>
                <?php foreach ( $albalu_events as $slug => $label ) : ?>
                    <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $albalu_current_event, $slug ); ?>><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="input-group flex-grow-1">
                <input type="search" class="form-control rounded-pill border-end-0 bg-light ps-4 py-2"
                       placeholder="<?php echo esc_attr_x( 'Cerca...', 'placeholder', 'bootscore' ); ?>"
                       value="<?php echo get_search_query(); ?>" name="s"
                       style="border-color: #EAE3E0;" />
                <button type="submit" class="btn btn-outline-secondary rounded-pill border-start-0 bg-light pe-4 py-2" style="border-color: #EAE3E0;" aria-label="Cerca">
                    <i class="fas fa-search text-muted"></i>
                </button>
            </div>
        </div>
    <?php else : ?>
        <div class="input-group">
            <input type="search" class="form-control rounded-pill border-end-0 bg-light ps-4 py-2"
                   placeholder="<?php echo esc_attr_x( 'Cerca...', 'placeholder', 'bootscore' ); ?>"
                   value="<?php echo get_search_query(); ?>" name="s"
                   style="border-color: #EAE3E0;" />
            <button type="submit" class="btn btn-outline-secondary rounded-pill border-start-0 bg-light pe-4 py-2" style="border-color: #EAE3E0;" aria-label="Cerca">
                <i class="fas fa-search text-muted"></i>
            </button>
        </div>
    <?php endif; ?>
</form>
