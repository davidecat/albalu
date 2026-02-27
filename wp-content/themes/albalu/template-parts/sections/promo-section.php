<?php

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
$index = isset( $args['index'] ) ? max( 1, (int) $args['index'] ) : 0;
$is_front = is_front_page();

if ( ! function_exists( 'get_field' ) ) {
    return;
}

$sections = get_field( 'promo_sections', $post_id );

$selected = null;
if ( is_array( $sections ) ) {
    if ( $index > 0 ) {
        $selected = isset( $sections[ $index - 1 ] ) ? $sections[ $index - 1 ] : null;
    } else {
        foreach ( $sections as $section ) {
            if ( is_array( $section ) && ( ! isset( $section['enabled'] ) || $section['enabled'] ) ) {
                $selected = $section;
                break;
            }
        }
    }
}

if ( is_array( $selected ) ) {
    echo albalu_render_promo_section( $selected );
    return;
}

if ( ! $is_front ) {
    return;
}

$fallback = array(
    'layout' => 'right',
    'subtitle' => 'Albalù Store',
    'title' => 'Rendiamo memorabile il <strong>tuo evento</strong>',
    'content' => '<p class="mb-4 text-secondary">Affidati alle mani di artigiani esperti che producono le bomboniere per le tue occasioni speciali rigorosamente in Italia.</p><p class="mb-4 text-secondary small">Su Albalù puoi trovare bomboniere originali ed utili, oppure puoi scegliere i complementi d\'arredo per la casa, regali per l\'infanzia e articoli religiosi creati mescolando cura del artigianato con l\'eleganza del design italiano.</p>',
    'btn_text' => 'Scopri i nostri prodotti',
    'btn_url' => '/shop/',
    'image' => '/wp-content/uploads/2026/01/08.webp',
);

echo albalu_render_promo_section( $fallback );
