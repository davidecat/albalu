<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFE\Classes
 */

namespace AdTribes\PFE\Classes;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Traits\Singleton_Trait;

/**
 * Extra Attributes class.
 *
 * @since 4.9.6
 */
class Extra_Attributes extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Holds the class instance object.
     *
     * @since 5.0.5
     * @access protected
     *
     * @var Product_Feed_Admin $instance The class instance object.
     */
    protected static $instance;

    /**
     * Get field definitions for extra attributes.
     *
     * @since 5.0.5
     * @access public
     *
     * @return array Field definitions.
     */
    public function get_field_definitions(): array {
        static $cached = null;
        if ( null !== $cached ) {
            return $cached;
        }
        // NOTE: translated labels/descriptions are locked in at the first call's locale.
        // Feed generation only reads $field['id'] from this result so locale staleness is
        // benign here, but consumers that need locale-correct labels mid-request (e.g.
        // across WPML language switches) must not rely on this cache.

        $energy_options = array(
            ''     => '',
            'A+++' => 'A+++',
            'A++'  => 'A++',
            'A+'   => 'A+',
            'A'    => 'A',
            'B'    => 'B',
            'C'    => 'C',
            'D'    => 'D',
            'E'    => 'E',
            'F'    => 'F',
            'G'    => 'G',
        );

        $cached = array(
            'general'  => array(
                'title'       => __( 'General', 'woo-product-feed-elite' ),
                'description' => __( 'Enable or disable general fields for the product.', 'woo-product-feed-elite' ),
                'fields'      => array(
                    array(
                        'id'          => '_woosea_brand',
                        'type'        => 'text',
                        'label'       => __( 'Brand', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter the product Brand here.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_gtin',
                        'type'        => 'text',
                        'label'       => __( 'GTIN', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter the product Global Trade Item Number (GTIN) here.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_mpn',
                        'type'        => 'text',
                        'label'       => __( 'MPN', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter the manufacturer product number', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_upc',
                        'type'        => 'text',
                        'label'       => __( 'UPC', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter the Universal Product Code (UPC) here.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_ean',
                        'type'        => 'text',
                        'label'       => __( 'EAN', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter the International Article Number (EAN) here.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_optimized_title',
                        'type'        => 'text',
                        'label'       => __( 'Optimized title', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter a optimized product title.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_condition',
                        'type'        => 'select',
                        'label'       => __( 'Product condition', 'woo-product-feed-elite' ),
                        'description' => __( 'Select the product condition.', 'woo-product-feed-elite' ),
                        'options'     => array(
                            ''            => '',
                            'new'         => __( 'new', 'woo-product-feed-elite' ),
                            'refurbished' => __( 'refurbished', 'woo-product-feed-elite' ),
                            'used'        => __( 'used', 'woo-product-feed-elite' ),
                            'damaged'     => __( 'damaged', 'woo-product-feed-elite' ),
                        ),
                    ),
                    array(
                        'id'          => '_woosea_color',
                        'type'        => 'text',
                        'label'       => __( 'Color', 'woo-product-feed-elite' ),
                        'description' => __( 'Insert a color.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_size',
                        'type'        => 'text',
                        'label'       => __( 'Size', 'woo-product-feed-elite' ),
                        'description' => __( 'Insert a size.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_gender',
                        'type'        => 'select',
                        'label'       => __( 'Gender', 'woo-product-feed-elite' ),
                        'description' => __( 'Select gender.', 'woo-product-feed-elite' ),
                        'options'     => array(
                            ''       => '',
                            'female' => __( 'female', 'woo-product-feed-elite' ),
                            'male'   => __( 'male', 'woo-product-feed-elite' ),
                            'unisex' => __( 'unisex', 'woo-product-feed-elite' ),
                        ),
                    ),
                    array(
                        'id'          => '_woosea_material',
                        'type'        => 'text',
                        'label'       => __( 'Material', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter a material.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_pattern',
                        'type'        => 'text',
                        'label'       => __( 'Pattern', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter a pattern.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_age_group',
                        'type'        => 'select',
                        'label'       => __( 'Age group', 'woo-product-feed-elite' ),
                        'description' => __( 'Select the product age group.', 'woo-product-feed-elite' ),
                        'options'     => array(
                            ''        => '',
                            'newborn' => __( 'newborn', 'woo-product-feed-elite' ),
                            'infant'  => __( 'infant', 'woo-product-feed-elite' ),
                            'toddler' => __( 'toddler', 'woo-product-feed-elite' ),
                            'kids'    => __( 'kids', 'woo-product-feed-elite' ),
                            'adult'   => __( 'adult', 'woo-product-feed-elite' ),
                        ),
                    ),
                    array(
                        'id'          => '_woosea_unit_pricing_measure',
                        'type'        => 'text',
                        'label'       => __( 'Unit pricing measure', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter an unit pricing measure.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_unit_pricing_base_measure',
                        'type'        => 'text',
                        'label'       => __( 'Unit pricing base measure', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter an unit pricing base measure.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_installment_months',
                        'type'        => 'text',
                        'label'       => __( 'Installment months', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter the number of monthly installments the buyer has to pay.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_installment_amount',
                        'type'        => 'text',
                        'label'       => __( 'Installment amount', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter the amount the buyer has to pay per month.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_cost_of_good_sold',
                        'type'        => 'text',
                        'label'       => __( 'Cost of goods sold', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter the cost of good you are selling.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_maximum_retail_price',
                        'type'        => 'text',
                        'label'       => __( 'Maximum retail price', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter the manufacturer\'s suggested retail price (MRP). Used by Google Shopping for the maximum_retail_price attribute (India only). Must match your landing page price.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_multipack',
                        'type'        => 'text',
                        'label'       => __( 'Multipack', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter the multipack amount.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_is_bundle',
                        'type'        => 'select',
                        'label'       => __( 'Is bundle', 'woo-product-feed-elite' ),
                        'description' => __( 'Select the is bundle value.', 'woo-product-feed-elite' ),
                        'options'     => array(
                            ''    => '',
                            'yes' => __( 'yes', 'woo-product-feed-elite' ),
                            'no'  => __( 'no', 'woo-product-feed-elite' ),
                        ),
                    ),
                    array(
                        'id'          => '_woosea_energy_efficiency_class',
                        'type'        => 'select',
                        'label'       => __( 'Energy efficiency class', 'woo-product-feed-elite' ),
                        'description' => __( 'Select the product energy efficiency class.', 'woo-product-feed-elite' ),
                        'options'     => $energy_options,
                    ),
                    array(
                        'id'          => '_woosea_min_energy_efficiency_class',
                        'type'        => 'select',
                        'label'       => __( 'Minimum energy efficiency class', 'woo-product-feed-elite' ),
                        'description' => __( 'Select the minimum product energy efficiency class.', 'woo-product-feed-elite' ),
                        'options'     => $energy_options,
                    ),
                    array(
                        'id'          => '_woosea_max_energy_efficiency_class',
                        'type'        => 'select',
                        'label'       => __( 'Maximum energy efficiency class', 'woo-product-feed-elite' ),
                        'description' => __( 'Select the maximum product energy efficiency class.', 'woo-product-feed-elite' ),
                        'options'     => $energy_options,
                    ),
                    array(
                        'id'          => '_woosea_is_promotion',
                        'type'        => 'text',
                        'label'       => __( 'Is promotion', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter your promotion ID.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_video',
                        'type'        => 'media',
                        'media_type'  => 'video',
                        'multiple'    => true,
                        'label'       => __( 'Video', 'woo-product-feed-elite' ),
                        'description' => __( 'Click to select video(s) from the media library.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_video_url',
                        'type'        => 'textarea',
                        'label'       => __( 'Video URL', 'woo-product-feed-elite' ),
                        'description' => sprintf(
                            /* translators: %s: line break tag */
                            __( 'Enter the video URL(s) here.%sOne URL per line. Up to 20 lines.', 'woo-product-feed-elite' ),
                            '<br/>'
                        ),
                    ),
                    array(
                        'id'          => '_woosea_custom_field_0',
                        'type'        => 'text',
                        'label'       => __( 'Custom field 0', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter your custom field 0', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_custom_field_1',
                        'type'        => 'text',
                        'label'       => __( 'Custom field 1', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter your custom field 1', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_custom_field_2',
                        'type'        => 'text',
                        'label'       => __( 'Custom field 2', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter your custom field 2', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_custom_field_3',
                        'type'        => 'text',
                        'label'       => __( 'Custom field 3', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter your custom field 3', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_custom_field_4',
                        'type'        => 'text',
                        'label'       => __( 'Custom field 4', 'woo-product-feed-elite' ),
                        'description' => __( 'Enter your custom field 4', 'woo-product-feed-elite' ),
                    ),
                ),
            ),
            'shipping' => array(
                'title'       => __( 'Shipping', 'woo-product-feed-elite' ),
                'description' => __( 'Configure shipping handling times for this product. Falls back to shipping class settings if empty.', 'woo-product-feed-elite' ),
                'tab'         => 'shipping',
                'fields'      => array(
                    array(
                        'id'          => '_woosea_min_handling_time',
                        'type'        => 'number',
                        'label'       => __( 'Min Handling Time (days)', 'woo-product-feed-elite' ),
                        'description' => __( 'Minimum time between order and shipment. Leave empty to use shipping class default.', 'woo-product-feed-elite' ),
                    ),
                    array(
                        'id'          => '_woosea_max_handling_time',
                        'type'        => 'number',
                        'label'       => __( 'Max Handling Time (days)', 'woo-product-feed-elite' ),
                        'description' => __( 'Maximum time between order and shipment. Leave empty to use shipping class default.', 'woo-product-feed-elite' ),
                    ),
                ),
            ),
            'open_ai'  => array(
                'title'       => __( 'OpenAI Flags', 'woo-product-feed-elite' ),
                'description' => __( 'Enable or disable OpenAI flags for the product.', 'woo-product-feed-elite' ),
                'fields'      => array(
                    array(
                        'id'          => '_woosea_open_ai_enable_search',
                        'type'        => 'checkbox',
                        'label'       => __( 'Enable search', 'woo-product-feed-elite' ),
                        'description' => __( 'Enable search', 'woo-product-feed-elite' ),
                        'default'     => 'yes',
                    ),
                    array(
                        'id'          => '_woosea_open_ai_enable_checkout',
                        'type'        => 'checkbox',
                        'label'       => __( 'Enable checkout', 'woo-product-feed-elite' ),
                        'description' => __( 'Enable checkout', 'woo-product-feed-elite' ),
                        'default'     => 'no',
                    ),
                ),
            ),
        );

        return $cached;
    }

    /**
     * Render a field based on its definition.
     *
     * @since 5.0.5
     * @access private
     *
     * @param array $field         Field definition.
     * @param array $extra_attributes Saved extra attributes.
     * @param int   $post_id       Current post ID.
     */
    private function render_field( array $field, array $extra_attributes, int $post_id ): void {
        $field_key = 'custom_attributes_' . $field['id'];

        // Check if field should be displayed.
        if ( ! array_key_exists( $field_key, $extra_attributes ) && empty( get_post_meta( $post_id, $field['id'], true ) ) ) {
            return;
        }

        $args = array(
            'id'          => $field['id'],
            'label'       => $field['label'],
            'desc_tip'    => 'true',
            'description' => $field['description'],
            'type'        => $field['type'],
        );

        // Handle different field types.
        switch ( $field['type'] ) {
            case 'select':
                $args['options'] = $field['options'];
                woocommerce_wp_select( $args );
                break;

            case 'checkbox':
                // Get current value, or use default if meta doesn't exist.
                $current_value = get_post_meta( $post_id, $field['id'], true );
                if ( '' === $current_value && isset( $field['default'] ) ) {
                    $args['value'] = $field['default'];
                }
                $args['checked_value']   = isset( $field['checked_value'] ) ? $field['checked_value'] : 'yes';
                $args['unchecked_value'] = isset( $field['unchecked_value'] ) ? $field['unchecked_value'] : 'no';
                woocommerce_wp_checkbox( $args );
                break;

            case 'textarea':
                woocommerce_wp_textarea_input( $args );
                break;

            case 'text':
            default:
                woocommerce_wp_text_input( $args );
                break;
        }
    }

    /**
     * Add extra attributes fields to the product edit page.
     *
     * @since 5.0.5
     * @access public
     */
    public function add_extra_attributes_fields() {
        global $post;

        $extra_attributes = get_option( 'adt_extra_attributes', array() );

        if ( empty( $extra_attributes ) ) {
            return;
        }

        $field_definitions = $this->get_field_definitions();

        echo '<div id="woosea_attr" class="options_group show_if_simple show_if_external show_if_variable">';

        // Iterate through sections.
        foreach ( $field_definitions as $section ) {
            // Skip if section is not general or defined to show in other tabs.
            if ( ! empty( $section['tab'] ) && 'general' !== $section['tab'] ) {
                continue;
            }

            // Iterate through fields in each section.
            foreach ( $section['fields'] as $field ) {
                $this->render_field( $field, $extra_attributes, $post->ID );
            }
        }

        // Always show exclude checkbox at the end.
        woocommerce_wp_checkbox(
            array(
                'id'          => '_woosea_exclude_product',
                'label'       => __( 'Exclude from feeds', 'woo-product-feed-elite' ),
                'desc_tip'    => 'true',
                'description' => __( 'Check this box if you want this product to be excluded from product feeds.', 'woo-product-feed-elite' ),
            )
        );

        echo '</div>';
    }

    /**
     * Add extra attributes shipping fields to the product edit page.
     *
     * @since 5.0.6
     * @access public
     */
    public function add_extra_attributes_shipping_fields() {
        global $post;

        $extra_attributes = get_option( 'adt_extra_attributes', array() );

        if ( empty( $extra_attributes ) ) {
            return;
        }

        $field_definitions = $this->get_field_definitions();

        echo '<div id="woosea_attr" class="options_group show_if_simple show_if_external show_if_variable">';

        // Iterate through sections.
        foreach ( $field_definitions as $section ) {
            // Skip if section is not shipping and defined to show in other tabs.
            if ( empty( $section['tab'] ) || ( isset( $section['tab'] ) && 'shipping' !== $section['tab'] ) ) {
                continue;
            }

            // Iterate through fields in each section.
            foreach ( $section['fields'] as $field ) {
                $this->render_field( $field, $extra_attributes, $post->ID );
            }
        }
        echo '</div>';
    }


    /**
     * Save custom fields for simple products.
     *
     * @since 5.0.5
     * @access public
     *
     * @param int $post_id The product post ID.
     */
    public function save_custom_fields( int $post_id ): void {
        // Check the nonce.
        $nonce = isset( $_POST['woocommerce_meta_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) : '';
        if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'woocommerce_save_data' ) ) {
            return;
        }

        $field_definitions = $this->get_field_definitions();

        // Save each field from all sections.
        foreach ( $field_definitions as $section ) {
            foreach ( $section['fields'] as $field ) {
                $field_id = $field['id'];

                // Use appropriate sanitization based on field type.
                if ( isset( $_POST[ $field_id ] ) ) {
                    if ( 'textarea' === $field['type'] ) {
                        $value = sanitize_textarea_field( wp_unslash( $_POST[ $field_id ] ) );
                    } else {
                        $value = sanitize_text_field( wp_unslash( $_POST[ $field_id ] ) );
                    }
                } else {
                    $value = '';
                }

                update_post_meta( $post_id, $field_id, $value );
            }
        }

        // Handle exclude product checkbox.
        $exclude_value = isset( $_POST['_woosea_exclude_product'] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_woosea_exclude_product', $exclude_value );
    }

    /**
     * Add custom fields for variable products.
     *
     * @since 5.0.5
     * @access public
     *
     * @param int    $loop         Loop index.
     * @param array  $variation_data Variation data.
     * @param object $variation    Variation post object.
     */
    public function add_variation_fields( int $loop, array $variation_data, $variation ): void {
        $extra_attributes = get_option( 'adt_extra_attributes', array() );

        if ( empty( $extra_attributes ) ) {
            return;
        }

        $field_definitions = $this->get_field_definitions();

        echo '<div id="woosea_attr" class="options_group">';
        echo '<hr><h4 class="woo-feed-variation-option-title">' . esc_html__( 'CUSTOM FIELDS (Product Feed ELITE for WooCommerce)', 'woo-product-feed-elite' ) . '</h4><hr>';

        // Iterate through sections.
        foreach ( $field_definitions as $section ) {
            echo '<h4>' . esc_html( $section['title'] ) . '</h4>';

            // Iterate through fields in each section.
            foreach ( $section['fields'] as $field ) {
                $field_key = 'custom_attributes_' . $field['id'];

                // Check if field should be displayed.
                if ( ! array_key_exists( $field_key, $extra_attributes ) && empty( get_post_meta( $variation->ID, $field['id'], true ) ) ) {
                    continue;
                }

                $this->render_variation_field( $field, $loop, $variation->ID );
            }
        }

        // Always show exclude checkbox.
        woocommerce_wp_checkbox(
            array(
                'id'          => '_woosea_exclude_product[' . $loop . ']',
                'label'       => __( '&nbsp;Exclude from feeds', 'woo-product-feed-elite' ),
                'placeholder' => __( 'Exclude from feeds', 'woo-product-feed-elite' ),
                'desc_tip'    => 'true',
                'description' => __( 'Check this box if you want this product to be excluded from product feeds.', 'woo-product-feed-elite' ),
                'value'       => get_post_meta( $variation->ID, '_woosea_exclude_product', true ),
            )
        );
        echo '<hr></div>';
    }

    /**
     * Render a variation field.
     *
     * @since 5.0.5
     * @access private
     *
     * @param array $field        Field definition.
     * @param int   $loop         Loop index.
     * @param int   $variation_id Variation ID.
     */
    private function render_variation_field( array $field, int $loop, int $variation_id ): void {
        $field_id    = str_replace( '_woosea_', '_woosea_variable_', $field['id'] );
        $field_value = get_post_meta( $variation_id, $field['id'], true );

        $args = array(
            'id'            => $field_id . '[' . $loop . ']',
            'label'         => $field['label'],
            'placeholder'   => $field['label'],
            'desc_tip'      => 'true',
            'description'   => $field['description'],
            'value'         => $field_value,
            'wrapper_class' => 'form-row form-row-full',
        );

        // Handle different field types.
        switch ( $field['type'] ) {
            case 'select':
                $args['options'] = $field['options'];
                woocommerce_wp_select( $args );
                break;

            case 'checkbox':
                // Fix checkbox label spacing.
                unset( $args['wrapper_class'] );
                $args['label'] = '&nbsp;' . $field['label'];

                // Get current value, or use default if meta doesn't exist.
                $current_value = get_post_meta( $variation_id, $field['id'], true );
                if ( '' === $current_value && isset( $field['default'] ) ) {
                    $args['value'] = $field['default'];
                }
                $args['checked_value']   = isset( $field['checked_value'] ) ? $field['checked_value'] : 'yes';
                $args['unchecked_value'] = isset( $field['unchecked_value'] ) ? $field['unchecked_value'] : 'no';
                woocommerce_wp_checkbox( $args );
                break;

            case 'textarea':
                woocommerce_wp_textarea_input( $args );
                break;

            case 'text':
            default:
                woocommerce_wp_text_input( $args );
                break;
        }
    }

    /**
     * Save custom fields for variable products.
     *
     * @since 5.0.5
     * @access public
     *
     * @param int $post_id The product post ID.
     */
    public function save_variation_fields( int $post_id ): void {
        // Check the nonce.
        check_ajax_referer( 'save-variations', 'security' );

        if ( ! isset( $_POST['variable_post_id'] ) || ! is_array( $_POST['variable_post_id'] ) ) {
            return;
        }

        $variable_post_id  = array_map( 'absint', wp_unslash( $_POST['variable_post_id'] ) );
        $max_loop          = max( array_keys( $variable_post_id ) );
        $field_definitions = $this->get_field_definitions();

        for ( $i = 0; $i <= $max_loop; $i++ ) {
            if ( ! isset( $variable_post_id[ $i ] ) ) {
                continue;
            }

            $variation_id = (int) $variable_post_id[ $i ];

            // Save each field from all sections.
            foreach ( $field_definitions as $section ) {
                foreach ( $section['fields'] as $field ) {
                    $field_id = str_replace( '_woosea_', '_woosea_variable_', $field['id'] );
                    $post_key = $field_id;

                    if ( isset( $_POST[ $post_key ][ $i ] ) ) {
                        // Use appropriate sanitization based on field type.
                        if ( 'textarea' === $field['type'] ) {
                            $value = sanitize_textarea_field( wp_unslash( $_POST[ $post_key ][ $i ] ) );
                        } else {
                            $value = sanitize_text_field( wp_unslash( $_POST[ $post_key ][ $i ] ) );
                        }
                        update_post_meta( $variation_id, $field['id'], $value );
                    }
                }
            }

            // Handle exclude product checkbox.
            $exclude_value = isset( $_POST['_woosea_exclude_product'][ $i ] ) ? sanitize_text_field( wp_unslash( $_POST['_woosea_exclude_product'][ $i ] ) ) : 'no';
            update_post_meta( $variation_id, '_woosea_exclude_product', $exclude_value );
        }
    }

    /**
     * Get field label by field key.
     *
     * @since 5.0.5
     * @access private
     *
     * @param string $field_key Field key (e.g., 'custom_attributes__woosea_brand').
     * @return string Field label or empty string if not found.
     */
    private function get_field_label_by_key( string $field_key ): string {
        $field_definitions = $this->get_field_definitions();

        foreach ( $field_definitions as $section ) {
            foreach ( $section['fields'] as $field ) {
                $current_key = 'custom_attributes_' . $field['id'];
                if ( $current_key === $field_key ) {
                    return $field['label'];
                }
            }
        }

        return '';
    }

    /**
     * Handle AJAX request to add or remove custom attributes.
     *
     * @since 5.0.5
     * @access public
     */
    public function ajax_manage_attributes(): void {
        // Verify nonce.
        check_ajax_referer( 'adt_manage_extra_attributes', 'security' );

        // Verify user capabilities.
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error(
                array( 'message' => __( 'You do not have permission to perform this action.', 'woo-product-feed-elite' ) ),
                403
            );
        }

        // Get and sanitize POST data.
        $attribute_name  = isset( $_POST['attribute_name'] ) ? sanitize_text_field( wp_unslash( $_POST['attribute_name'] ) ) : '';
        $attribute_value = isset( $_POST['attribute_value'] ) ? sanitize_text_field( wp_unslash( $_POST['attribute_value'] ) ) : '';
        $active          = isset( $_POST['active'] ) ? sanitize_text_field( wp_unslash( $_POST['active'] ) ) : '';

        if ( empty( $attribute_name ) || empty( $attribute_value ) ) {
            wp_send_json_error(
                array( 'message' => __( 'Invalid attribute data.', 'woo-product-feed-elite' ) ),
                400
            );
        }

        $extra_attributes = get_option( 'adt_extra_attributes', array() );

        // Get the field label from definitions.
        $attribute_label = $this->get_field_label_by_key( $attribute_value );

        if ( 'true' === $active ) {
            // Add attribute.
            $extra_attributes[ $attribute_value ] = $attribute_label;
        } elseif ( 'false' === $active && isset( $extra_attributes[ $attribute_value ] ) ) {
            // Remove attribute.
            unset( $extra_attributes[ $attribute_value ] );
        }

        update_option( 'adt_extra_attributes', $extra_attributes, false );

        wp_send_json_success(
            array(
                /* translators: 1: Attribute name, 2: Attribute status */
                'message'    => sprintf( __( 'Attribute %1$s: %2$s.', 'woo-product-feed-elite' ), $attribute_label, 'true' === $active ? __( 'Enabled', 'woo-product-feed-elite' ) : __( 'Disabled', 'woo-product-feed-elite' ) ),
                'attributes' => $extra_attributes,
            )
        );
    }

    /**
     * Add extra attributes to the product data.
     *
     * @since 5.0.5
     * @access public
     *
     * @param array       $product_data The product data.
     * @param array       $feed         The feed data.
     * @param \WC_Product $product      The product data.
     * @return array
     */
    public function add_extra_attributes_to_product_data( $product_data, $feed, $product ) {
        if ( empty( $product_data ) ) {
            return $product_data;
        }

        $extra_attributes = get_option( 'adt_extra_attributes', array() );
        if ( ! is_array( $extra_attributes ) ) {
            $extra_attributes = array();
        }

        // Clear stale values for Elite extras that have been disabled. The Pro plugin's
        // get_custom_attributes() (class-get-products.php) iterates raw wp_postmeta and
        // unconditionally populates `custom_attributes_<meta_key>` for every product, so
        // disabled extras keep leaking their previously-saved values into the feed until
        // the meta row itself is deleted. Strip those keys here for any Elite-defined
        // field that is no longer enabled.
        foreach ( $this->get_field_definitions() as $section ) {
            foreach ( $section['fields'] as $field ) {
                $field_key = 'custom_attributes_' . $field['id'];
                if ( ! array_key_exists( $field_key, $extra_attributes ) ) {
                    unset( $product_data[ $field_key ] );
                }
            }
        }

        foreach ( $extra_attributes as $field_key => $field_label ) {
            $meta_key = str_replace( 'custom_attributes_', '', $field_key );

            if ( $product->is_type( 'variation' ) ) {
                // If variation, check if meta exists.
                // If exists, then get the meta value from the variation.
                // If not exists, then get the meta value from the parent product.
                $meta_exists = $product->meta_exists( $meta_key );
                if ( $meta_exists ) {
                    $meta_value = $product->get_meta( $meta_key, true );
                } else {
                    $parent_product = wc_get_product( $product->get_parent_id() );
                    if ( $parent_product ) {
                        $meta_value = $parent_product->get_meta( $meta_key, true );
                    }
                }
            } else {
                $meta_value = $product->get_meta( $meta_key, true );
            }

            /**
             * Filter the extra attribute value.
             *
             * @since 5.0.5
             * @access public
             *
             * @param mixed     $meta_value The meta value.
             * @param string    $field_key  The field key.
             * @param string    $meta_key   The meta key.
             * @param \WC_Product $product    The product object.
             * @return mixed
             */
            $meta_value = apply_filters( 'adt_product_data_extra_attribute_value', $meta_value, $field_key, $meta_key, $product );

            // Only overwrite the product data if we have a non-empty meta value.
            // For Elite extras that are still enabled, this preserves the Pro-side fallbacks
            // wired up in class-get-products.php (optimized title -> product title, condition
            // -> product condition). Disabled extras never reach this loop because the strip
            // pass above unsets their $product_data keys, so the Pro fallback is intentionally
            // cleared along with the stale meta — that is the behaviour change this fix
            // introduces for issue #339.
            if ( '' !== $meta_value || ! isset( $product_data[ $field_key ] ) ) {
                $product_data[ $field_key ] = $meta_value;
            }
        }

        return $product_data;
    }

    /**
     * Run the class
     *
     * @codeCoverageIgnore
     * @since 5.0.5
     */
    public function run() {
        // Simple product fields.
        add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_extra_attributes_fields' ) );
        add_action( 'woocommerce_product_options_shipping_product_data', array( $this, 'add_extra_attributes_shipping_fields' ) );
        add_action( 'woocommerce_process_product_meta', array( $this, 'save_custom_fields' ) );

        // Variable product fields.
        add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'add_variation_fields' ), 10, 3 );
        add_action( 'woocommerce_save_product_variation', array( $this, 'save_variation_fields' ), 10, 1 );

        // AJAX handler for managing attributes.
        add_action( 'wp_ajax_woosea_elite_add_attributes', array( $this, 'ajax_manage_attributes' ) );

        add_filter( 'adt_get_product_data', array( $this, 'add_extra_attributes_to_product_data' ), 10, 3 );
    }
}
