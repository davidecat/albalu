<?php
//phpcs:disable
/**
 * Get product variation ID based on dropdown selects product page.
 */
function woosea_elite_storedattributes_details() {
    // checking the nonce. will die if it is no good.
    check_ajax_referer( 'woosea_ajax_nonce', 'nonce' );

    $user          = wp_get_current_user();
    $allowed_roles = array( 'administrator' );

    if ( array_intersect( $allowed_roles, $user->roles ) ) {
        if ( isset( $_POST['data_to_pass'] ) ) {
            $productId = sanitize_text_field( $_POST['data_to_pass'] );

            // Remove previous drop-down selection.
            delete_option( 'selected_values' );

            // Good idea to make sure things are set before using them.
            $selected_values = isset( $_POST['storedAttributes'] ) ? (array) $_POST['storedAttributes'] : array();

            // Any of the WordPress data sanitization functions can be used here.
            $selected_values = array_map( 'esc_attr', $selected_values );

            // Save drop-down selection.
            update_option( 'selected_values', $selected_values, false );
        }
    }
}
add_action( 'wp_ajax_nopriv_woosea_elite_storedattributes_details', 'woosea_elite_storedattributes_details' );
add_action( 'wp_ajax_woosea_elite_storedattributes_details', 'woosea_storedattributes_details' );

/**
 * Get details to load in the Facebook AddToCart event (pixel).
 */
function woosea_elite_addtocart_details() {
    // checking the nonce. will die if it is no good.
    check_ajax_referer( 'woosea_ajax_nonce', 'nonce' );

    $user          = wp_get_current_user();
    $allowed_roles = array( 'administrator' );

    if ( array_intersect( $allowed_roles, $user->roles ) ) {
        $productId   = sanitize_text_field( $_POST['data_to_pass'] );
        $variationId = 0;

        if ( ! empty( $productId ) ) {
            $product         = wc_get_product( $productId );
            $selected_values = get_option( 'selected_values' );

            unset( $selected_values['productId'] );

            $_GET         = $selected_values;
            $variation_id = woosea_elite_find_matching_product_variation( $product, $_GET );

            if ( $variation_id > 0 ) {
                $productId = $variation_id;
            }

            $nr_get                = count( $_GET );
            $product_name          = $product->get_name();
            $product_type          = $product->get_type();
            $product_price         = $product->get_price();
            $product_regular_price = $product->get_regular_price();
            $product_sale_price    = $product->get_sale_price();
            $product_sku           = $product->get_sku();
            $currency              = get_woocommerce_currency();
            $cats                  = '';
            $all_cats              = get_the_terms( $productId, 'product_cat' );

            if ( ! empty( $all_cats ) ) {
                foreach ( $all_cats as $key => $category ) {
                    $cats .= $category->name . ',';
                }
            }

            // strip last comma.
            $cats = rtrim( $cats, ',' );
            $cats = str_replace( '&amp;', '&', $cats );
            $data = array(
                'product_id'            => $productId,
                'product_name'          => $product_name,
                'product_type'          => $product_type,
                'product_price'         => $product_price,
                'product_regular_price' => $product_regular_price,
                'product_sale_price'    => $product_sale_price,
                'product_sku'           => $product_sku,
                'product_currency'      => $currency,
                'product_cats'          => $cats,
            );

            echo json_encode( $data );
            wp_die();
        }
    }
}
add_action( 'wp_ajax_nopriv_woosea_elite_addtocart_details', 'woosea_elite_addtocart_details' );
add_action( 'wp_ajax_woosea_elite_addtocart_details', 'woosea_elite_addtocart_details' );

/**
 * Add some JS and mark-up code on every front-end page in order to get the conversion tracking to work.
 */
function woosea_elite_hook_header() {
    $marker = sprintf( '<!-- This website runs the Product Feed ELITE for WooCommerce by AdTribes.io plugin -->' );
    echo "\n{$marker}\n";
}
add_action( 'wp_head', 'woosea_elite_hook_header' );

/**
 * We need to be able to make an AJAX call on the thank you page.
 *
 * @param int $order_id The order ID.
 */
function woosea_elite_inject_ajax( $order_id ) {
    // Last order details.
    $order    = new WC_Order( $order_id );
    $order_id = $order->get_id();
    update_option( 'last_order_id', $order_id, false );
}
add_action( 'woocommerce_thankyou', 'woosea_elite_inject_ajax' );

/**
 * Sanitize XSS.
 *
 * @param string $value The value to sanitize.
 *
 * @return string The sanitized value.
 */
function woosea_elite_sanitize_xss( $value ) {
    return htmlspecialchars( strip_tags( $value ) );
}

/**
 * Retrieve variation product id based on it attributes.
 *
 * @param object $product The product object.
 * @param array  $attributes The attributes.
 *
 * @return int The product ID.
 **/
function woosea_elite_find_matching_product_variation( $product, $attributes ) {

    foreach ( $attributes as $key => $value ) {
        if ( str_starts_with( $key, 'attribute_' ) ) {
            continue;
        }
        unset( $attributes[ $key ] );
        $attributes[ sprintf( 'attribute_%s', $key ) ] = $value;
    }

    if ( class_exists( 'WC_Data_Store' ) ) {
        $data_store = WC_Data_Store::load( 'product' );
        return $data_store->find_matching_product_variation( $product, $attributes );
    } else {
        return $product->get_matching_variation( $attributes );
    }
}

/**
 * Remove the price from the JSON-LD on variant product pages
 * As WooCommerce shows the wrong price and it causes items
 * to disapproved in Google's Merchant center because of it.
 *
 * @param object $product The product object.
 */
function woosea_elite_product_delete_meta_price( $product = null ) {
    $markup_offer        = array();
    $structured_data_fix = get_option( 'adt_structured_data_fix' );

    if ( ! is_object( $product ) ) {
        global $product;
    }

    if ( ! is_a( $product, 'WC_Product' ) ) {
        return;
    }

    $shop_name     = get_bloginfo( 'name' );
    $shop_url      = home_url();
    $shop_currency = get_woocommerce_currency();

    // Display URL of current page.
    if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) {
        $link = 'https';
    } else {
        $link = 'http';
    }
    // Here append the common URL characters.
    $link .= '://';

    // Append the host(domain name, ip) to the URL.
    $link .= $_SERVER['HTTP_HOST'];

    // Append the requested resource location to the URL.
    $link .= $_SERVER['REQUEST_URI'];

    if ( $structured_data_fix == 'yes' ) {
        $pr_woo     = wc_get_price_to_display( $product );
        $product_id = get_the_id();

        // Get product MPN.
        $mpn = get_post_meta( $product_id, '_woosea_mpn', true );

        // Get product condition.
        $condition = get_post_meta( $product_id, '_woosea_condition', true );
        $condition = is_string( $condition ) ? ucfirst( $condition ) : $condition;

        if ( empty( $condition ) ) {
            $json_condition = 'NewCondition';
        } else {
            $json_condition = $condition . 'Condition';
        }

        // Assume prices will be valid until the end of next year, unless on sale and there is an end date.
        $price_valid_until = date( 'Y-12-31', time() + YEAR_IN_SECONDS );

        if ( ! $product ) {
            return -1;
        }

        if ( $product->is_type( 'variable' ) ) {
            // We should first check if there are any _GET parameters available.
            // When there are not we are on a variable product page but not on a specific variable one.
            // In that case we need to put in the AggregateOffer structured data.
            $nr_get = count( $_GET );

            if ( $nr_get > 0 ) {
                $children_ids = $product->get_children();

                foreach ( $children_ids as &$child_val ) {
                    $product_variations = new WC_Product_Variation( $child_val );
                    $variations         = array_filter( $product_variations->get_variation_attributes() );
                    $from_url           = str_replace( '\\', '', $_GET, $i );
                    $intersect          = array_intersect( $from_url, $variations );

                    if ( $variations == $intersect ) {
                        $variation_id = $child_val;
                    }
                }

                if ( isset( $variation_id ) ) {
                    $variable_product = wc_get_product( $variation_id );
                }

                if ( ( isset( $variation_id ) ) && ( is_object( $variable_product ) ) ) {
                    $qty = 1;

                    // on default show prices including tax.
                    $product_price  = wc_get_price_including_tax( $variable_product );
                    $structured_vat = get_option( 'adt_structured_vat' );

                    // user requested to have prices without tax.
                    if ( $structured_vat == 'yes' ) {
                        $tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class() );

                        // Workaround for price caching issues.
                        if ( ! empty( $tax_rates ) ) {
                            $tax_rates[1]['rate'] = 0;
                        }

                        // Make sure tax rates are numeric.
                        if ( is_numeric( $tax_rates[1]['rate'] ) ) {
                            if ( is_numeric( $variable_product->get_price() ) ) {
                                $product_price = wc_get_price_excluding_tax( $variable_product, array( 'price' => $variable_product->get_price() ) ) * ( 100 + $tax_rates[1]['rate'] ) / 100;
                            }
                        }
                    }

                    // Force rounding to two decimals.
                    if ( ! empty( $product_price ) ) {
                        $product_price = round( $product_price, 2 );
                    }

                    // Get product MPN.
                    $mpn = get_post_meta( $variation_id, '_woosea_mpn', true );

                    // Get product condition.
                    $condition = get_post_meta( $variation_id, '_woosea_condition', true );
                    $condition = is_string( $condition ) ? ucfirst( $condition ) : $condition;

                    if ( ! empty( $condition ) ) {
                        $json_condition = 'NewCondition';
                    } else {
                        $json_condition = $condition . 'Condition';
                    }

                    // Get stock status.
                    $stock_status = $variable_product->get_stock_status();

                    if ( $stock_status == 'outofstock' ) {
                        $availability = 'OutOfStock';
                    } else {
                        $availability = 'InStock';
                    }

                    if ( $variable_product->is_on_sale() && $variable_product->get_date_on_sale_to() ) {
                        $price_valid_until = date( 'Y-m-d', $variable_product->get_date_on_sale_to()->getTimestamp() );
                    }

                    $markup_offer = array(
                        '@type'              => 'Offer',
                        'price'              => $product_price,
                        'priceValidUntil'    => $price_valid_until,
                        'priceSpecification' => array(
                            '@type'                 => 'PriceSpecification',
                            'price'                 => $product_price,
                            'priceCurrency'         => $shop_currency,
                            'valueAddedTaxIncluded' => wc_prices_include_tax() ? 'true' : 'false',
                        ),
                        'priceCurrency'      => $shop_currency,
                        'itemCondition'      => 'https://schema.org/' . $json_condition . '',
                        'availability'       => 'https://schema.org/' . $availability . '',
                        'sku'                => $variable_product->get_sku(),
                        'image'              => wp_get_attachment_url( $product->get_image_id() ),
                        'description'        => $product->get_description(),
                        'seller'             => array(
                            '@type' => 'Organization',
                            'name'  => $shop_name,
                            'url'   => $shop_url,
                        ),
                        'url'                => $link,
                    );
                } else {
                    // AggregateOffer.
                    $prices  = $product->get_variation_prices();
                    $lowest  = reset( $prices['price'] );
                    $highest = end( $prices['price'] );

                    $price_valid_until = date( 'Y-m-d', $variable_product->get_date_on_sale_to()->getTimestamp() );

                    if ( $lowest === $highest ) {
                        $markup_offer = array(
                            '@type'              => 'Offer',
                            'price'              => wc_format_decimal( $lowest, wc_get_price_decimals() ),
                            'priceCurrency'      => $shop_currency,
                            'priceValidUntil'    => $price_valid_until,
                            'priceSpecification' => array(
                                '@type'                 => 'PriceSpecification',
                                'price'                 => wc_format_decimal( $lowest, wc_get_price_decimals() ),
                                'priceCurrency'         => $shop_currency,
                                'valueAddedTaxIncluded' => wc_prices_include_tax() ? 'true' : 'false',
                            ),
                            'itemCondition'      => 'https://schema.org/' . $json_condition . '',
                            'availability'       => 'https://schema.org/' . $stock = ( $product->is_in_stock() ? 'InStock' : 'OutOfStock' ),
                            'sku'                => $product->get_sku(),
                            'image'              => wp_get_attachment_url( $product->get_image_id() ),
                            'description'        => $product->get_description(),
                            'seller'             => array(
                                '@type' => 'Organization',
                                'name'  => $shop_name,
                                'url'   => $shop_url,
                            ),
                            'url'                => $link,
                        );
                    } else {
                        $markup_offer = array(
                            '@type'           => 'AggregateOffer',
                            'lowPrice'        => wc_format_decimal( $lowest, wc_get_price_decimals() ),
                            'highPrice'       => wc_format_decimal( $highest, wc_get_price_decimals() ),
                            'priceCurrency'   => $shop_currency,
                            'priceValidUntil' => $price_valid_until,
                            'itemCondition'   => 'https://schema.org/' . $json_condition . '',
                            'availability'    => 'https://schema.org/' . $stock = ( $product->is_in_stock() ? 'InStock' : 'OutOfStock' ),
                            'sku'             => $product->get_sku(),
                            'image'           => wp_get_attachment_url( $product->get_image_id() ),
                            'description'     => $product->get_description(),
                            'seller'          => array(
                                '@type' => 'Organization',
                                'name'  => $shop_name,
                                'url'   => $shop_url,
                            ),
                            'url'             => $link,
                        );
                    }
                }
            } else {
                // This is a variation product page but no variation has been selected. WooCommerce always shows the price of the lowest priced.
                // variation product. That is why we also put this in the JSON.
                // When there are no parameters in the URL (so for normal users, not coming via Google Shopping URL's) show the old WooCommwerce JSON.
                $product_price = wc_get_price_to_display( $product );

                // Get default variation product price if set.
                $default_attributes = $product->get_default_attributes();
                if ( $default_attributes ) {
                    $default_variation_id = woosea_elite_find_matching_product_variation( $product, $default_attributes );
                    if ( $default_variation_id > 0 ) {
                        $default_variation = wc_get_product( $default_variation_id );
                        if ( is_a( $default_variation, 'WC_Product' ) ) {
                            $product_price = wc_get_price_to_display( $default_variation );
                        }
                    }
                }

                if ( ! is_string( $product_price ) ) {
                    $product_price = round( $product_price, 2 );
                }

                $markup_offer += array(
                    '@type'         => 'Offer',
                    'price'         => $product_price,
                    'priceCurrency' => $shop_currency,
                    'availability'  => 'https://schema.org/' . ( $product->is_in_stock() ? 'InStock' : 'OutOfStock' ),
                    'sku'           => $product->get_sku(),
                    'seller'        => array(
                        '@type' => 'Organization',
                        'name'  => $shop_name,
                        'url'   => $shop_url,
                    ),
                    'url'           => $link,
                );
            }
        } else {
            // This is a simple product.
            // By default show prices including tax.
            $product_price = wc_get_price_including_tax( $product );

            $structured_vat = get_option( 'adt_structured_vat' );

            // Use prices excluding tax.
            if ( $structured_vat == 'yes' ) {
                $tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class() );
                if ( ! isset( $tax_rates[1]['rate'] ) ) {
                    $tax_rates[1]['rate'] = 0;
                }

                if ( is_numeric( $tax_rates[1]['rate'] ) ) {
                    if ( isset( $variable_product ) ) {
                        if ( is_numeric( $variable_product->get_price() ) ) {
                            $product_price = wc_get_price_excluding_tax( $product, array( 'price' => $product->get_price() ) ) * ( 100 + $tax_rates[1]['rate'] ) / 100;
                        }
                    }
                }
            }
            $product_price = round( $product_price, 2 );

            // Assume prices will be valid until the end of next year, unless on sale and there is an end date.
            $price_valid_until = date( 'Y-12-31', time() + YEAR_IN_SECONDS );

            $markup_offer = array(
                '@type'              => 'Offer',
                'price'              => $product_price,
                'priceValidUntil'    => $price_valid_until,
                'priceCurrency'      => $shop_currency,
                'priceSpecification' => array(
                    '@type'                 => 'PriceSpecification',
                    'price'                 => $product_price,
                    'priceCurrency'         => $shop_currency,
                    'valueAddedTaxIncluded' => wc_prices_include_tax() ? 'true' : 'false',
                ),
                'itemCondition'      => 'https://schema.org/' . $json_condition . '',
                'availability'       => 'https://schema.org/' . $stock = ( $product->is_in_stock() ? 'InStock' : 'OutOfStock' ),
                'sku'                => $product->get_sku(),
                'image'              => wp_get_attachment_url( $product->get_image_id() ),
                'description'        => $product->get_description(),
                'seller'             => array(
                    '@type' => 'Organization',
                    'name'  => $shop_name,
                    'url'   => $shop_url,
                ),
                'url'                => $link,
            );
        }
    } else {
        // Just use the old WooCommerce buggy setting.
        if ( '' !== $product->get_price() ) {

            $price_valid_until = date( 'Y-12-31', time() + YEAR_IN_SECONDS );

            if ( ! $product ) {
                return -1;
            }

            if ( $product->is_type( 'variable' ) ) {
                $prices  = $product->get_variation_prices();
                $lowest  = reset( $prices['price'] );
                $highest = end( $prices['price'] );

                if ( $lowest === $highest ) {

                    $markup_offer = array(
                        '@type'              => 'Offer',
                        'price'              => wc_format_decimal( $lowest, wc_get_price_decimals() ),
                        'priceValidUntil'    => $price_valid_until,
                        'priceCurrency'      => $shop_currency,
                        'priceSpecification' => array(
                            'price'                 => wc_format_decimal( $lowest, wc_get_price_decimals() ),
                            'priceCurrency'         => $shop_currency,
                            'valueAddedTaxIncluded' => wc_prices_include_tax() ? 'true' : 'false',
                        ),
                    );
                } else {
                    $markup_offer = array(
                        '@type'           => 'AggregateOffer',
                        'lowPrice'        => wc_format_decimal( $lowest, wc_get_price_decimals() ),
                        'highPrice'       => wc_format_decimal( $highest, wc_get_price_decimals() ),
                        'priceValidUntil' => $price_valid_until,
                        'priceCurrency'   => $shop_currency,
                        'availability'    => 'https://schema.org/' . ( $product->is_in_stock() ? 'InStock' : 'OutOfStock' ),
                        'seller'          => array(
                            '@type' => 'Organization',
                            'name'  => $shop_name,
                            'url'   => $shop_url,
                        ),
                    );
                }
            } else {
                if ( $product->is_on_sale() && $product->get_date_on_sale_to() ) {
                    $price_valid_until = date( 'Y-m-d', $product->get_date_on_sale_to()->getTimestamp() );
                }

                $permalink    = get_permalink( $product->get_id() );
                $markup_offer = array(
                    '@type'              => 'Offer',
                    'price'              => wc_format_decimal( $product->get_price(), wc_get_price_decimals() ),
                    'priceValidUntil'    => $price_valid_until,
                    'priceCurrency'      => $shop_currency,
                    'priceSpecification' => array(
                        'price'                 => wc_format_decimal( $product->get_price(), wc_get_price_decimals() ),
                        'priceCurrency'         => $shop_currency,
                        'valueAddedTaxIncluded' => wc_prices_include_tax() ? 'true' : 'false',
                    ),
                    'availability'       => 'https://schema.org/' . ( $product->is_in_stock() ? 'InStock' : 'OutOfStock' ),
                    'url'                => $permalink,
                    'seller'             => array(
                        '@type' => 'Organization',
                        'name'  => $shop_name,
                        'url'   => $shop_url,
                    ),
                );
            }
        }
    }
    return $markup_offer;
}

/**
 * Fix the WooCommerce schema markup bug for variation prices.
 *
 * @param array $markup The structured data schema markup.
 * @param object $product The product object.
 *
 * @return array The structured data schema markup.
 */
function woosea_elite_product_fix_structured_data( $markup, $product = null ) {
    if ( ! is_object( $product ) ) {
        global $product;
    }
    
    if ( ! is_a( $product, 'WC_Product' ) ) {
        return $markup;
    }

    $markup = array();

    $shop_name = get_bloginfo( 'name' );
    $shop_url  = home_url();
    $currency  = get_woocommerce_currency();

    // Sisplay URL of current page.
    if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) {
        $link = 'https';
    } else {
        $link = 'http';
    }
    // Here append the common URL characters.
    $link .= '://';
    // Append the host(domain name, ip) to the URL.
    $link .= $_SERVER['HTTP_HOST'];
    // Append the requested resource location to the URL.
    $link .= $_SERVER['REQUEST_URI'];

    $structured_data_fix = get_option( 'adt_structured_data_fix' );

    // This is an Elite user who enababled the structured data fix.
    if ( $structured_data_fix == 'yes' ) {
        // We should first check if there are any _GET parameters available.
        // When there are not we are on a variable product page but not on a specific variable one.
        // In that case we need to put in the AggregateOffer structured data.

        // Strip off UTM parameters from GET.
        foreach ( $_GET as $key => $value ) {
            if ( preg_match( '/utm/', $key ) ) {
                unset( $_GET[ $key ] );
            }
        }

        $nr_get = count( $_GET );

        if ( $nr_get > 0 ) {
            // This is a variable product.
            $children_ids = $product->get_children();
            $prod_type    = $product->get_type();

            if ( $prod_type == 'variable' ) {
                foreach ( $children_ids as &$child_val ) {
                    $product_variations = new WC_Product_Variation( $child_val );
                    $variations         = array_filter( $product_variations->get_variation_attributes() );
                    $from_url           = str_replace( '\\', '', $_GET, $i );
                    $intersect          = array_intersect( $from_url, $variations );
                    if ( $variations == $intersect ) {
                        $variation_id = $child_val;
                    }
                }
            }

            if ( isset( $variation_id ) ) {
                $variable_product = wc_get_product( $variation_id );
            }

            if ( ( isset( $variation_id ) ) && ( is_object( $variable_product ) ) ) {
                $markup = array(
                    '@type'       => 'Product',
                    '@id'         => $link . '#product', // Append '#product' to differentiate between this @id and the @id generated for the Breadcrumblist.
                    'name'        => $variable_product->get_name(),
                    'url'         => $link,
                    'description' => wp_strip_all_tags( do_shortcode( $product->get_short_description() ? $product->get_short_description() : $product->get_description() ) ),
                );
                $image  = wp_get_attachment_url( $variable_product->get_image_id() );
                if ( $image ) {
                    $markup['image'] = $image;
                }

                // Get product brand.
                $brand = get_post_meta( $variation_id, '_woosea_brand', true );
                if ( $brand ) {
                    $markup['brand'] = array(
                        '@type' => 'Brand',
                        'name'  => $brand,
                    );
                }

                // Get product MPN.
                $mpn = get_post_meta( $variation_id, '_woosea_mpn', true );
                if ( $mpn ) {
                    $markup['mpn'] = $mpn;
                }

                // Get product GTIM.
                $gtin = get_post_meta( $variation_id, '_woosea_gtin', true );

                if ( $gtin ) {
                    $gtin_length = strlen( $gtin );

                    if ( $gtin_length == 14 ) {
                        $markup['gtin14'] = $gtin;
                    } elseif ( $gtin_length == 13 ) {
                        $markup['gtin13'] = $gtin;
                    } elseif ( $gtin_length == 12 ) {
                        $markup['gtin12'] = $gtin;
                    } elseif ( $gtin_length == 8 ) {
                        $markup['gtin8'] = $gtin;
                    } else {
                        // do not add GTIN to markup.
                    }
                }

                // Declare SKU or fallback to ID.
                if ( $variable_product->get_sku() ) {
                    $markup['sku'] = $variable_product->get_sku();
                } else {
                    $markup['sku'] = $variable_product->get_id();
                }

                // Get the offers structured data schema markup.
                $markup['offers'][0] = woosea_elite_product_delete_meta_price( $product );

                // This only works for WooCommerce 3.6 and higher (wc_review_ratings_enabled function).
                if ( ( $product->get_rating_count() > 0 ) || ( function_exists( wc_review_ratings_enabled() ) ) ) {
                    $markup['aggregateRating'] = array(
                        '@type'       => 'AggregateRating',
                        'ratingValue' => $product->get_average_rating(),
                        'reviewCount' => $product->get_review_count(),
                    );

                    // Markup 5 most recent rating/review.
                    $comments = get_comments(
                        array(
                            'number'      => 5,
                            'post_id'     => $product->get_id(),
                            'status'      => 'approve',
                            'post_status' => 'publish',
                            'post_type'   => 'product',
                            'parent'      => 0,
                            'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                                array(
                                    'key'     => 'rating',
                                    'type'    => 'NUMERIC',
                                    'compare' => '>',
                                    'value'   => 0,
                                ),
                            ),
                        )
                    );

                    if ( $comments ) {
                        $markup['review'] = array();
                        foreach ( $comments as $comment ) {
                            $markup['review'][] = array(
                                '@type'         => 'Review',
                                'reviewRating'  => array(
                                    '@type'       => 'Rating',
                                    'ratingValue' => get_comment_meta( $comment->comment_ID, 'rating', true ),
                                ),
                                'author'        => array(
                                    '@type' => 'Person',
                                    'name'  => get_comment_author( $comment ),
                                ),
                                'reviewBody'    => get_comment_text( $comment ),
                                'datePublished' => get_comment_date( 'c', $comment ),
                            );
                        }
                    }
                }
            }
        } else {
            // This is a simple product.
            $markup = array(
                '@type'       => 'Product',
                '@id'         => $link . '#product', // Append '#product' to differentiate between this @id and the @id generated for the Breadcrumblist.
                'name'        => $product->get_name(),
                'url'         => $link,
                'description' => wp_strip_all_tags( do_shortcode( $product->get_short_description() ? $product->get_short_description() : $product->get_description() ) ),
            );

            $brand = get_post_meta( $product->get_id(), '_woosea_brand', true );

            if ( $brand ) {
                $markup['brand'] = array(
                    '@type' => 'Brand',
                    'name'  => $brand,
                );
            }

            // Get product MPN.
            $mpn = get_post_meta( $product->get_id(), '_woosea_mpn', true );

            if ( $mpn ) {
                $markup['mpn'] = $mpn;
            }

            // Get product GTIM.
            $gtin = get_post_meta( $product->get_id(), '_woosea_gtin', true );

            if ( $gtin ) {
                $gtin_length = strlen( $gtin );

                if ( $gtin_length == 14 ) {
                    $markup['gtin14'] = $gtin;
                } elseif ( $gtin_length == 13 ) {
                    $markup['gtin13'] = $gtin;
                } elseif ( $gtin_length == 12 ) {
                    $markup['gtin12'] = $gtin;
                } elseif ( $gtin_length == 8 ) {
                    $markup['gtin8'] = $gtin;
                } else {
                    // do not add GTIN to markup.
                }
            }

            $image = wp_get_attachment_url( $product->get_image_id() );
            if ( $image ) {
                $markup['image'] = $image;
            }

            // Declare SKU or fallback to ID.
            if ( $product->get_sku() ) {
                $markup['sku'] = $product->get_sku();
            } else {
                $markup['sku'] = $product->get_id();
            }

            // Get the offers structured data schema markup.
            $markup['offers'][0] = woosea_elite_product_delete_meta_price( $product );

            if ( ( $product->get_rating_count() ) || ( function_exists( wc_review_ratings_enabled() ) ) ) {
                $markup['aggregateRating'] = array(
                    '@type'       => 'AggregateRating',
                    'ratingValue' => $product->get_average_rating(),
                    'reviewCount' => $product->get_review_count(),
                );

                // Markup 5 most recent rating/review.
                $comments = get_comments(
                    array(
                        'number'      => 5,
                        'post_id'     => $product->get_id(),
                        'status'      => 'approve',
                        'post_status' => 'publish',
                        'post_type'   => 'product',
                        'parent'      => 0,
                        'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                            array(
                                'key'     => 'rating',
                                'type'    => 'NUMERIC',
                                'compare' => '>',
                                'value'   => 0,
                            ),
                        ),
                    )
                );

                if ( $comments ) {
                    $markup['review'] = array();
                    foreach ( $comments as $comment ) {
                        $markup['review'][] = array(
                            '@type'         => 'Review',
                            'reviewRating'  => array(
                                '@type'       => 'Rating',
                                'ratingValue' => get_comment_meta( $comment->comment_ID, 'rating', true ),
                            ),
                            'author'        => array(
                                '@type' => 'Person',
                                'name'  => get_comment_author( $comment ),
                            ),
                            'reviewBody'    => get_comment_text( $comment ),
                            'datePublished' => get_comment_date( 'c', $comment ),
                        );
                    }
                }
            }
        }
    } else {
        // Structured data fix is not enabled.
        $markup = array(
            '@type'       => 'Product',
            '@id'         => $link . '#product', // Append '#product' to differentiate between this @id and the @id generated for the Breadcrumblist.
            'name'        => $product->get_name(),
            'url'         => $link,
            'description' => wp_strip_all_tags( do_shortcode( $product->get_short_description() ? $product->get_short_description() : $product->get_description() ) ),
        );

        $image = wp_get_attachment_url( $product->get_image_id() );
        if ( $image ) {
            $markup['image'] = $image;
        }

        // Declare SKU or fallback to ID.
        if ( $product->get_sku() ) {
            $markup['sku'] = $product->get_sku();
        } else {
            $markup['sku'] = $product->get_id();
        }

        // Get the offers structured data schema markup.
        $markup['offers'][0] = woosea_elite_product_delete_meta_price( $product );

        // Check if Yoast SEO WooCommerce plugin is enabled.
        if ( ( $product->get_rating_count() ) || ( function_exists( wc_review_ratings_enabled() ) ) ) {
            $markup['aggregateRating'] = array(
                '@type'       => 'AggregateRating',
                'ratingValue' => $product->get_average_rating(),
                'reviewCount' => $product->get_review_count(),
            );

            // Markup 5 most recent rating/review.
            $comments = get_comments(
                array(
                    'number'      => 5,
                    'post_id'     => $product->get_id(),
                    'status'      => 'approve',
                    'post_status' => 'publish',
                    'post_type'   => 'product',
                    'parent'      => 0,
                    'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                        array(
                            'key'     => 'rating',
                            'type'    => 'NUMERIC',
                            'compare' => '>',
                            'value'   => 0,
                        ),
                    ),
                )
            );

            if ( $comments ) {
                $markup['review'] = array();
                foreach ( $comments as $comment ) {
                    $markup['review'][] = array(
                        '@type'         => 'Review',
                        'reviewRating'  => array(
                            '@type'       => 'Rating',
                            'ratingValue' => get_comment_meta( $comment->comment_ID, 'rating', true ),
                        ),
                        'author'        => array(
                            '@type' => 'Person',
                            'name'  => get_comment_author( $comment ),
                        ),
                        'reviewBody'    => get_comment_text( $comment ),
                        'datePublished' => get_comment_date( 'c', $comment ),
                    );
                }
            }
        }
    }

    $data = $markup;
    return $data;
}

// Only execute this filter when the structured data fix is enabled.
$structured_data_fix = get_option( 'adt_structured_data_fix' );
if ( $structured_data_fix == 'yes' ) {
    if ( class_exists( 'WPSEO_WooCommerce_Schema' ) ) {
        add_filter( 'wpseo_schema_product', 'woosea_elite_product_fix_structured_data', 10, 2 );
    } else {
        add_filter( 'woocommerce_structured_data_product', 'woosea_elite_product_fix_structured_data', 10, 2 );
    }
}

/**
 * Get the dynamic attributes.
 */
function woosea_elite_special_attributes() {
    $attributes_obj           = new WooSEA_Attributes();
    $special_attributes       = $attributes_obj->get_special_attributes_dropdown();
    $special_attributes_clean = $attributes_obj->get_special_attributes_clean();

    $data = array(
        'dropdown' => $special_attributes,
        'clean'    => $special_attributes_clean,
    );

    echo json_encode( $data );
    wp_die();
}
add_action( 'wp_ajax_woosea_elite_special_attributes', 'woosea_elite_special_attributes' );

/**
 * Add or remove custom attributes to the feed configuration drop-downs.
 */
function woosea_elite_add_attributes() {
    $attribute_name  = sanitize_text_field( $_POST['attribute_name'] );
    $attribute_value = sanitize_text_field( $_POST['attribute_value'] );
    $active          = sanitize_text_field( $_POST['active'] );

    if ( ! get_option( 'adt_extra_attributes' ) ) {
        if ( $active == 'true' ) {
            $extra_attributes = array(
                $attribute_value => $attribute_name,
            );
            update_option( 'adt_extra_attributes', $extra_attributes, false );
        }
    } else {
        $extra_attributes = get_option( 'adt_extra_attributes' );
        if ( ! in_array( $attribute_name, $extra_attributes, true ) ) {
            if ( $active == 'true' ) {
                $add_attribute    = array(
                    $attribute_value => $attribute_name,
                );
                $extra_attributes = array_merge( $extra_attributes, $add_attribute );
                update_option( 'adt_extra_attributes', $extra_attributes, false );
            }
        } elseif ( $active == 'false' ) {
            // remove from extra attributes array.
            $extra_attributes = array_diff( $extra_attributes, array( $attribute_value => $attribute_name ) );
            update_option( 'adt_extra_attributes', $extra_attributes, false );
        }
    }
    $extra_attributes = get_option( 'adt_extra_attributes' );
}
add_action( 'wp_ajax_woosea_elite_add_attributes', 'woosea_elite_add_attributes' );

/**
 * Set tracking cookies.
 */
function woosea_elite_set_cookie() {

    if ( ! empty( $_POST['adTribesID'] ) ) {
        $adTribesID = sanitize_text_field( $_POST['adTribesID'] );

        // Conversion cookie will expire in 30 days from now. Make this configurable later.
        $number_of_days = 30;
        $date_of_expiry = time() + 60 * 60 * 24 * $number_of_days;
        setcookie( 'adTribesID', $adTribesID, $date_of_expiry );

        $success = 'yes';
        $data    = array(
            'cookie_set' => $success,
        );

        $data = json_encode( $data );
        echo $data;
        wp_die();
    }
}
add_action( 'wp_ajax_woosea_elite_set_cookie', 'woosea_elite_set_cookie' );
add_action( 'wp_ajax_nopriv_woosea_elite_set_cookie', 'woosea_elite_set_cookie' );

/**
 * This function saves the status of a product before changes are made to it.
 * We need this to determine if a product is updated and thus feeds need to refresh.
 *
 * @param int $post_id The ID of the product that is being saved.
 */
function woosea_elite_before_product_save( $post_id ) {
    $post_type = get_post_type( $post_id );
    if ( $post_type == 'product' ) {
        $product = wc_get_product( $post_id );

        if ( is_object( $product ) ) {
            $product_data = $product->get_data();

            $before = array(
                'product_id'        => $post_id,
                'type'              => $product->get_type(),
                'name'              => $product->get_name(),
                'slug'              => $product->get_slug(),
                'status'            => $product->get_status(),
                'featured'          => $product->get_featured(),
                'visibility'        => $product->get_catalog_visibility(),
                'description'       => $product->get_description(),
                'short_description' => $product->get_short_description(),
                'sku'               => $product->get_sku(),
                'price'             => $product->get_price(),
                'regular_price'     => $product->get_regular_price(),
                'sale_price'        => $product->get_sale_price(),
                'total_sales'       => $product->get_total_sales(),
                'tax_status'        => $product->get_tax_status(),
                'tax_class'         => $product->get_tax_class(),
                'manage_stock'      => $product->get_manage_stock(),
                'stock_quantity'    => $product->get_stock_quantity(),
                'stock_status'      => $product->get_stock_status(),
                'backorders'        => $product->get_backorders(),
                'weight'            => $product->get_weight(),
                'length'            => $product->get_length(),
                'width'             => $product->get_width(),
                'height'            => $product->get_height(),
                'parent_id'         => $product->get_parent_id(),
            );

            if ( ! get_option( 'product_changes' ) ) {
                update_option( 'product_changes', $before, false );
            }
        }
    }
}
add_action( 'pre_post_update', 'woosea_elite_before_product_save' );

/**
 * Detect changes made to products.
 * When no changes are made feed(s) do not need to get updated.
 *
 * @param int $product_id The ID of the product that is being saved.
 */
function woosea_elite_on_product_save( $product_id ) {
    $product = wc_get_product( $product_id );

    if ( is_object( $product ) ) {
        $product_data = $product->get_data();

        $after = array(
            'product_id'        => $product_id,
            'type'              => $product->get_type(),
            'name'              => $product->get_name(),
            'slug'              => $product->get_slug(),
            'status'            => $product->get_status(),
            'featured'          => $product->get_featured(),
            'visibility'        => $product->get_catalog_visibility(),
            'description'       => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'sku'               => $product->get_sku(),
            'price'             => $product->get_price(),
            'regular_price'     => $product->get_regular_price(),
            'sale_price'        => $product->get_sale_price(),
            'total_sales'       => $product->get_total_sales(),
            'tax_status'        => $product->get_tax_status(),
            'tax_class'         => $product->get_tax_class(),
            'manage_stock'      => $product->get_manage_stock(),
            'stock_quantity'    => $product->get_stock_quantity(),
            'stock_status'      => $product->get_stock_status(),
            'backorders'        => $product->get_backorders(),
            'sold_individually' => $product->get_sold_individually(),
            'weight'            => $product->get_weight(),
            'length'            => $product->get_length(),
            'width'             => $product->get_width(),
            'height'            => $product->get_height(),
            'parent_id'         => $product->get_parent_id(),
        );

        if ( is_array( $product_data ) ) {

            if ( get_option( 'product_changes' ) ) {
                $before = get_option( 'product_changes' );
                $diff   = array_diff( $after, $before );
                if ( ! $diff ) {
                    $diff['product_id'] = $product_id;
                } else {
                    // Enable the prodyct changed flag.
                    update_option( 'woosea_allow_update', 'no', false );
                }
                delete_option( 'product_changes' );
            } else {
                // Enable the prodyct changed flag.
                update_option( 'woosea_allow_update', 'no', false );
            }
        }
    }
}
add_action( 'woocommerce_update_product', 'woosea_elite_on_product_save', 10, 1 );

/**
 * Creates the RSS metabox.
 */
function woosea_elite_my_rss_box() {

    // Get RSS Feed(s)
    include_once ABSPATH . WPINC . '/feed.php';
    $domain = $_SERVER['HTTP_HOST'];

    // My feeds list (add your own RSS feeds urls).
    $my_feeds = array(
        'https://www.adtribes.io/feed/',
    );

    // Loop through Feeds.
    foreach ( $my_feeds as $feed ) :
        // Get a SimplePie feed object from the specified feed source.
        $rss = fetch_feed( $feed );

        $maxitems  = 0;
        $rss_items = array();
        $rss_title = '';

        if ( ! is_wp_error( $rss ) ) : // Checks that the object is created correctly.
            // Figure out how many total items there are, and choose a limit.
            $maxitems = $rss->get_item_quantity( 5 );

            // Build an array of all the items, starting with element 0 (first element).
            $rss_items = $rss->get_items( 0, $maxitems );

            // Get RSS title.
            $rss_title = '<a href="' . $rss->get_permalink() . '" target="_blank">' . strtoupper( $rss->get_title() ) . '</a>';
            // endif;

            // Display the container.
            echo '<div class="rss-widget">';
            echo '<strong>' . $rss_title . '</strong>';
            echo '<hr style="border: 0; background-color: #DFDFDF; height: 1px;">';

            // Starts items listing within <ul> tag.
            echo '<ul>';

            // Check items
            if ( $maxitems == 0 ) {
                echo '<li>' . __( 'No item', 'rc_mdm' ) . '.</li>';
            } else {
                // Loop through each feed item and display each item as a hyperlink.
                foreach ( $rss_items as $item ) :
                    // Uncomment line below to display non human date.
                    // $item_date = $item->get_date( get_option('date_format').' @ '.get_option('time_format') );

                    // Get human date (comment if you want to use non human date).
                    $item_date = human_time_diff( $item->get_date( 'U' ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'rc_mdm' );

                    // Start displaying item content within a <li> tag.
                    echo '<li>';
                    // create item link.
                    echo '<a href="' . esc_url( $item->get_permalink() ) . '?utm_source=pfe&utm_medium=plugin&utm_campaign=dashboard-rss" title="' . $item_date . '" target="_blank">';
                    // Get item title.
                    echo esc_html( $item->get_title() );
                    echo '</a>';
                    // Display date.
                    echo ' <span class="rss-date">' . $item_date . '</span><br />';
                    // Get item content.
                    $content = $item->get_content();
                    // Shorten content.
                    $content = wp_html_excerpt( $content, 120 ) . ' [...]';
                    // Display content.
                    echo $content;
                    // End <li> tag.
                    echo '</li>';
                endforeach;
            }
            // End <ul> tag.
            echo '</ul>';
            echo '<hr style="border: 0; background-color: #DFDFDF; height: 1px;">';
            echo '<a href="https://adtribes.io/tutorials/?utm_source=pfe&utm_medium=plugin&utm_campaign=dashboard-rss" target="_blank">More tutorials on our website</a>';
            echo '</div>';
        endif;
    endforeach; // End foreach feed.
}
