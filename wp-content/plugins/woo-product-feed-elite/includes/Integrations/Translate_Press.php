<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Integrations
 */

namespace AdTribes\PFE\Integrations;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Traits\Singleton_Trait;
use AdTribes\PFE\Helpers\Product_Feed_Helper;
use AdTribes\PFE\Helpers\Helper;
use AdTribes\PFE\Classes\Product_Feed_Attributes;

/**
 * Translate_Press class.
 *
 * @since 4.9.7
 */
class Translate_Press extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Check if Translate Press plugin is active.
     *
     * @since 4.9.7
     * @return bool
     */
    public function is_active() {
        return is_plugin_active( 'translatepress-multilingual/index.php' ) && get_option( 'adt_enable_translatepress_support' ) === 'yes';
    }

    /**
     * Add Translate Press data to product feed.
     *
     * @since 4.9.7
     * @param array $data The accumulated filter data.
     * @return array
     */
    public function add_translatepress_data( $data ) {
        $data['translatepress'] = '';
        return $data;
    }

    /**
     * Show Translate Press language field.
     *
     * @since 4.9.7
     * @param array $feed Feed data.
     */
    public function show_translatepress_language_field( $feed ) {
        $trp                     = \TRP_Translate_Press::get_trp_instance();
        $trp_languages           = $trp->get_component( 'languages' );
        $trp_settings            = $trp->get_component( 'settings' );
        $trp_published_languages = $trp_languages->get_language_names( $trp_settings->get_settings()['publish-languages'] );
        $trp_selected_language   = $trp_settings->get_setting( 'default-language' );

        // If not new feed, get the feed translatepress data.
        if ( $feed ) {
            // If feed doesn't have language selected, use current language.
            $feed_trp_language     = $feed->translatepress ?? '';
            $trp_selected_language = ! empty( $feed_trp_language ) ? $feed_trp_language : $trp_selected_language;
        }

        Helper::locate_admin_template(
            'integrations/translatepress/translatepress-general-settings-language-field.php',
            true,
            true,
            array(
                'trp_languages'           => $trp_languages,
                'trp_settings'            => $trp_settings,
                'trp_published_languages' => $trp_published_languages,
                'trp_selected_language'   => $trp_selected_language,
            )
        );
    }

    /**
     * Create product feed translatepress data props.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array        $props     The product feed properties.
     * @param Product_Feed $feed      The feed data.
     * @param array        $post_data The post data from the legacy code base.
     * @return array
     */
    public function create_product_feed_props( $props, $feed, $post_data ) {
        $props['translatepress'] = sanitize_text_field( $post_data['TRANSLATEPRESS'] ?? '' ); // phpcs:disable WordPress.Security.NonceVerification
        return $props;
    }

    /**
     * Edit product feed translatepress data props.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array        $props_to_update The product feed properties to update.
     * @param Product_Feed $product_feed    The product feed.
     * @return array
     */
    public function edit_product_feed_props( $props_to_update, $product_feed ) {
        $props_to_update['translatepress'] = sanitize_text_field( wp_unslash( $_POST['TRANSLATEPRESS'] ?? '' ) ); // phpcs:disable WordPress.Security.NonceVerification
        return $props_to_update;
    }

    /**
     * Clone product feed translatepress data before save.
     *
     * @since 4.9.7
     * @access public
     *
     * @param Product_Feed $feed          The product feed.
     * @param Product_Feed $original_feed The original feed.
     */
    public function clone_product_feed_before_save( $feed, $original_feed ) {
        $feed->set_prop( 'translatepress', $original_feed->translatepress );
    }


    /**
     * Override product feed strings with Translate Press.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array        $product_data The product data.
     * @param Product_Feed $feed         The feed data.
     * @param WC_Product   $product      The product data.
     * @return string
     */
    public function translate_product_data( $product_data, $feed, $product ) {
        if ( empty( $product_data ) ) {
            return $product_data;
        }

        $trp                  = \TRP_Translate_Press::get_trp_instance();
        $trp_settings         = $trp->get_component( 'settings' );
        $trp_default_language = $trp_settings->get_setting( 'default-language' );
        $trp_feed_language    = $feed->translatepress ?? '';

        if ( empty( $trp_feed_language ) || $trp_default_language === $trp_feed_language ) {
            return $product_data;
        }

        $attributes           = Product_Feed_Attributes::instance()->get_attributes();
        $strings_to_translate = array();
        if ( ! empty( $attributes ) ) {
            foreach ( $attributes as $key => $attribute ) {
                foreach ( $attribute as $attribute_key => $attribute_value ) {
                    $strings_to_translate[] = $attribute_key;
                }
            }
        }

        /**
         * Filter the strings to be translated by Translate Press.
         *
         * @since 4.9.7
         *
         * @param array        $strings_to_translate The strings to be translated.
         * @param array        $product_data         The product data.
         * @param Product_Feed $feed                 The feed data.
         * @param WC_Product   $product              The product data.
         */
        $strings_to_translate = apply_filters(
            'adt_translatepress_product_data_strings',
            $strings_to_translate,
            $product_data,
            $feed,
            $product
        );

        /**
         * Get the post object for fallback strings.
         */
        $post_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
        $post    = get_post( $post_id );
        if ( ! $post ) {
            return $product_data;
        }

        // Gather all strings to translate and their keys.
        $strings_map = array();
        foreach ( $strings_to_translate as $key ) {
            if ( ! isset( $product_data[ $key ] ) || empty( $product_data[ $key ] ) || ! is_string( $product_data[ $key ] ) ) {
                continue;
            }

            $string = '';
            switch ( $key ) {
                case 'description':
                    $raw_desc = $product->get_description() ? $product->get_description() : $post->post_content;
                    // Apply content filters to match what TranslatePress stored from frontend.
                    $string = apply_filters( 'the_content', $raw_desc );
                    break;
                case 'short_description':
                    $raw_short = $product->get_short_description() ? $product->get_short_description() : $post->post_excerpt;
                    // Apply excerpt filters to match what TranslatePress stored from frontend.
                    $string = apply_filters( 'the_excerpt', $raw_short );
                    break;
                case 'mother_description':
                    // Apply content filters to match what TranslatePress stored from frontend.
                    $string = apply_filters( 'the_content', $post->post_content );
                    break;
                case 'mother_short_description':
                    // Apply excerpt filters to match what TranslatePress stored from frontend.
                    $string = apply_filters( 'the_excerpt', $post->post_excerpt );
                    break;
                case 'title':
                    // Apply title filters to match what TranslatePress stored from frontend.
                    $string = apply_filters( 'the_title', $product->get_name(), $post_id );
                    break;
                case 'title_hyphen':
                    // Apply title filters to match what TranslatePress stored from frontend.
                    $string = apply_filters( 'the_title', $product->get_name(), $post_id ) . ' - ';
                    break;
                case 'mother_title':
                    // Apply title filters to match what TranslatePress stored from frontend.
                    $string = apply_filters( 'the_title', $post->post_title, $post_id );
                    break;
                case 'mother_title_hyphen':
                    // Apply title filters to match what TranslatePress stored from frontend.
                    $string = apply_filters( 'the_title', $post->post_title, $post_id ) . ' - ';
                    break;
                default:
                    $string = $product_data[ $key ];
                    break;
            }

            /**
             * Filter the string to be translated by Translate Press.
             * This filter is used to modify the string before it's translated.
             *
             * @since 4.9.7
             *
             * @param string       $string       The string to be translated.
             * @param string       $key          The string key.
             * @param array        $product_data The product data.
             * @param Product_Feed $feed         The feed data.
             * @param WC_Product   $product      The product data.
             * @return string
             */
            $string = apply_filters( 'adt_translatepress_product_data_string', $string, $key, $product_data, $feed, $product );

            if ( ! empty( $string ) ) {
                $strings_map[ $key ] = $string;
            }
        }

        if ( empty( $strings_map ) ) {
            return $product_data;
        }

        // TranslatePress works by breaking down content into smaller chunks and translating each chunk.
        // We need to set up the global post context so TranslatePress can properly process the content.
        global $post;
        $original_post = $post;
        $post          = get_post( $post_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        setup_postdata( $post );

        // Use TranslatePress's official API to switch language context.
        // This properly handles all global variables and filters needed for translation.
        // trp_switch_language() sets $TRP_LANGUAGE, $TRP_LANGUAGE_COPY, and adds the trp_reset_language filter
        // to ensure the language persists throughout the translation process.
        if ( function_exists( 'trp_switch_language' ) ) {
            trp_switch_language( $trp_feed_language );
        }

        foreach ( $strings_map as $key => $original_string ) {
            $translated_string = $original_string;

            // Use trp_translate() to handle content translation.
            // We pass null as the language parameter since we've already switched the language context via trp_switch_language().
            // It will automatically break down the content and translate the parts found in the database.
            if ( function_exists( 'trp_translate' ) ) {
                $translated_string = trp_translate( $original_string, null, false );
            }

            // Remove TranslatePress preview mode tags that were added during translation.
            // These tags contain metadata attributes (data-trp-translate-id, data-trp-node-group, etc.)
            // that are used in the editor but shouldn't appear in the feed output.
            $translated_string = $this->remove_translatepress_preview_tags( $translated_string );

            // Re-sanitize the translated string.
            switch ( $key ) {
                case 'title':
                case 'mother_title':
                case 'description':
                case 'short_description':
                case 'mother_description':
                case 'mother_short_description':
                    if ( class_exists( '\AdTribes\PFP\Helpers\Sanitization' ) && method_exists( '\AdTribes\PFP\Helpers\Sanitization', 'sanitize_html_content' ) ) {
                        $translated_string = \AdTribes\PFP\Helpers\Sanitization::sanitize_html_content( $translated_string, $feed );
                    }
                    break;
            }

            /**
             * Filter the sanitized translated string.
             * This filter is used to modify the sanitized translated string.
             *
             * @since 4.9.7
             *
             * @param string       $translated_string The sanitized translated string.
             * @param string       $key               The string key.
             * @param array        $product_data      The product data.
             * @param Product_Feed $feed              The feed data.
             * @param WC_Product   $product           The product data.
             * @return string
             */
            $translated_string = apply_filters( 'adt_translatepress_product_data_string_sanitized', $translated_string, $key, $product_data, $feed, $product );

            $product_data[ $key ] = $translated_string;
        }

        // Restore original TranslatePress language using the official API.
        // trp_restore_language() removes the trp_reset_language filter and restores $TRP_LANGUAGE.
        if ( function_exists( 'trp_restore_language' ) ) {
            trp_restore_language();
        }

        // Restore original post context.
        $post = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        if ( $post ) {
            setup_postdata( $post );
        } else {
            wp_reset_postdata();
        }

        return $product_data;
    }

    /**
     * Remove TranslatePress preview mode tags from translated content.
     *
     * When using preview mode, TranslatePress adds <translate-press> wrapper tags
     * with metadata attributes. This method removes those tags while preserving
     * the translated content.
     *
     * @since 5.0.6
     * @access private
     *
     * @param string $content The translated content that may contain TranslatePress tags.
     * @return string The content with TranslatePress tags removed.
     */
    private function remove_translatepress_preview_tags( $content ) {
        if ( empty( $content ) || ! is_string( $content ) ) {
            return $content;
        }

        // Remove <translate-press> opening tags with or without attributes, and self-closing tags.
        $content = preg_replace( "/(<|&lt;)translate-press(\s+(?:[^>\"']|\"[^\"]*\"|'[^']*')*?)?\s*\/?(>|&gt;)/i", '', $content );

        // Remove closing </translate-press> tags.
        $content = preg_replace( '/(<|&lt;)\/translate-press(>|&gt;)/i', '', $content );

        // Remove <trp-wrap> opening tags with or without attributes, and self-closing tags.
        $content = preg_replace( "/(<|&lt;)trp-wrap(\s+(?:[^>\"']|\"[^\"]*\"|'[^']*')*?)?\s*\/?(>|&gt;)/i", '', $content );

        // Remove closing </trp-wrap> tags.
        $content = preg_replace( '/(<|&lt;)\/trp-wrap(>|&gt;)/i', '', $content );

        return $content;
    }

    /**
     * Convert product data links with Translate Press.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array $product_data The product data.
     * @param array $feed The feed data.
     * @param array $product The product data.
     * @return array
     */
    public function convert_product_data_links( $product_data, $feed, $product ) {
        if ( empty( $product_data ) ) {
            return $product_data;
        }

        $trp                  = \TRP_Translate_Press::get_trp_instance();
        $trp_settings         = $trp->get_component( 'settings' );
        $trp_url_converter    = $trp->get_component( 'url_converter' );
        $trp_default_language = $trp_settings->get_setting( 'default-language' );
        $trp_feed_language    = $feed->translatepress ?? '';

        if ( empty( $trp_feed_language ) || $trp_default_language === $trp_feed_language ) {
            return $product_data;
        }

        $links_to_convert = array( 'link', 'link_no_tracking', 'variable_link', 'category_link', 'add_to_cart_link', 'cart_link' );
        foreach ( $links_to_convert as $key ) {
            if ( isset( $product_data[ $key ] ) && ! empty( $product_data[ $key ] ) && filter_var( $product_data[ $key ], FILTER_VALIDATE_URL ) ) {
                // Translate Press URL converter has rtrim() deprecated issue, so we need to suppress the error here.
                $converted_link = @$trp_url_converter->get_url_for_language( $trp_feed_language, $product_data[ $key ], '' ); // phpcs:ignore
                $product_data[ $key ] = esc_url_raw( $converted_link );
            }
        }

        return $product_data;
    }

    /**
     * Translate categories dropdown.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array $categories The categories array of WP_Term objects or category names.
     * @param array $cat_args The category arguments.
     * @param int   $feed_id The feed ID.
     * @return array
     */
    public function translate_categories_dropdown( $categories, $cat_args, $feed_id ) {
        // If categories are empty, return them as is.
        if ( empty( $categories ) ) {
            return $categories;
        }

        $trp                  = \TRP_Translate_Press::get_trp_instance();
        $trp_settings         = $trp->get_component( 'settings' );
        $trp_default_language = $trp_settings->get_setting( 'default-language' );
        $trp_feed_language    = '';

        // If new feed, get the translatepress language from the project temp.
        if ( 0 === $feed_id ) {
            $project_temp      = get_option( ADT_OPTION_TEMP_PRODUCT_FEED, array() );
            $trp_feed_language = $project_temp['TRANSLATEPRESS'] ?? '';
        } else {
            $feed              = Product_Feed_Helper::get_product_feed( $feed_id );
            $trp_feed_language = $feed->translatepress ?? '';
        }

        // If feed language is the default language, no need to translate.
        if ( empty( $trp_feed_language ) || $trp_default_language === $trp_feed_language || ! function_exists( 'trp_translate' ) ) {
            return $categories;
        }

        $translated_categories = array();
        foreach ( $categories as $key => $category ) {
            if ( is_object( $category ) && $category instanceof \WP_Term ) {
                // Clone the term object to avoid modifying the original.
                $translated_term = clone $category;
                // Translate the category name and remove any preview mode tags.
                $translated_name               = trp_translate( $category->name, $trp_feed_language, false );
                $translated_term->name         = $this->remove_translatepress_preview_tags( $translated_name );
                $translated_categories[ $key ] = $translated_term;
            } elseif ( is_string( $category ) ) {
                // Translate the category name string and remove any preview mode tags.
                $translated_name               = trp_translate( $category, $trp_feed_language, false );
                $translated_categories[ $key ] = $this->remove_translatepress_preview_tags( $translated_name );
            } else {
                // Keep original for any other type.
                $translated_categories[ $key ] = $category;
            }
        }

        return $translated_categories;
    }

    /**
     * Run 4.9.7 integration hooks.
     *
     * @since 4.9.7
     */
    public function run() {
        if ( ! $this->is_active() ) {
            return;
        }

        // Add extra data to product feed.
        add_filter( 'adt_product_feed_data', array( $this, 'add_translatepress_data' ) );

        // Show Translate Press language field.
        add_action( 'adt_general_feed_settings_before_country_field', array( $this, 'show_translatepress_language_field' ), 60, 1 );

        // Add Translate Press data to product feed on creation.
        add_filter( 'adt_create_product_feed_props', array( $this, 'create_product_feed_props' ), 70, 3 );

        // Add Translate Press data to product feed.
        add_filter( 'adt_edit_feed_general_tab_props', array( $this, 'edit_product_feed_props' ), 60, 2 );

        // Clone Translate Press data.
        add_action( 'adt_clone_product_feed_before_save', array( $this, 'clone_product_feed_before_save' ), 60, 2 );

        // Convert product data language.
        add_filter( 'adt_get_product_data', array( $this, 'translate_product_data' ), 60, 3 );

        // Convert links with Translate Press.
        add_filter( 'adt_get_product_data', array( $this, 'convert_product_data_links' ), 60, 3 );

        // Translate categories dropdown.
        add_filter( 'adt_pfp_get_categories_dropdown', array( $this, 'translate_categories_dropdown' ), 60, 3 );
    }
}
