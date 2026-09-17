<?php
/**
 * Portable (cross-site) export/import of a product's add-on groups and fields.
 *
 * Unlike admin/functions-admin-import-export-aou.php (which copies groups between
 * products/global on the SAME site by post ID), this serialises a product's whole
 * add-on configuration to a self-contained JSON document that can be imported onto
 * a product on a DIFFERENT site. Images are referenced by URL and downloaded on
 * import, or - when the "Embed images in add-on exports" setting is on -
 * base64-embedded in the file so import needs no network access. References that
 * cannot cross a site boundary (product IDs, variation IDs, category terms) are
 * dropped and reported.
 *
 * A bundled demo library lives in demo-products/*.json - users can pick one from a
 * dropdown on the product edit screen and have its add-ons created without an upload.
 *
 * @since 4.4.6
 * @package WooCommerce Product Add-Ons Ultimate
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! defined( 'PEWC_PORTABLE_FORMAT' ) ) {
	define( 'PEWC_PORTABLE_FORMAT', 'pewc-product-addons' );
}
if( ! defined( 'PEWC_PORTABLE_FORMAT_VERSION' ) ) {
	define( 'PEWC_PORTABLE_FORMAT_VERSION', 1 );
}

/**
 * Capability required to use the portable export/import tools.
 * @since 4.4.6
 */
function pewc_portable_capability() {
	return apply_filters( 'pewc_portable_export_capability', 'manage_woocommerce' );
}

/**
 * Whether the file-based "Transfer Add-Ons Between Sites" export/import is enabled.
 * Off by default; enabled via the "Enable file import/export" setting.
 * (The bundled demo library is a separate setting - see pewc_portable_library_enabled().)
 * @since 4.4.6
 */
function pewc_portable_file_transfer_enabled() {
	$enabled = 'yes' === get_option( 'pewc_enable_file_import_export', 'no' );
	return (bool) apply_filters( 'pewc_portable_file_transfer_enabled', $enabled );
}

/**
 * Whether the "Install Add-Ons from Library" (bundled demo products) section
 * is enabled. Off by default; enabled via the "Enable add-ons library" setting.
 * @since 4.4.6
 */
function pewc_portable_library_enabled() {
	$enabled = 'yes' === get_option( 'pewc_enable_addons_library', 'no' );
	return (bool) apply_filters( 'pewc_portable_library_enabled', $enabled );
}

/* -------------------------------------------------------------------------
 * Field-param classification
 * ---------------------------------------------------------------------- */

/**
 * Field params that hold a single WordPress attachment ID.
 * @since 4.4.6
 */
function pewc_portable_attachment_params() {
	return apply_filters( 'pewc_portable_attachment_params', array( 'field_image' ) );
}

/**
 * Field params that reference other products, variations, categories or fields
 * and so cannot be carried across sites. Each maps to the report bucket it lands in.
 * @since 4.4.6
 */
function pewc_portable_unportable_params() {
	return apply_filters( 'pewc_portable_unportable_params', array(
		'child_products'         => 'dropped_product_refs',
		'child_qty_product_id'   => 'dropped_product_refs',
		'products_field_id'      => 'dropped_field_refs',
		'reverse_formula_field'  => 'dropped_field_refs',
		'reverse_input_field'    => 'dropped_field_refs',
		'parent_swatch_id'       => 'dropped_field_refs',
		'variation_field'        => 'dropped_variation_refs',
		'child_categories'       => 'dropped_category_refs',
	) );
}

/**
 * Field params the admin templates iterate over with foreach(). A non-array in
 * one of these fatals the product edit screen, so on import we normalise:
 * empty -> array(), a lone scalar -> array( scalar ). Anything not in this list
 * (including comma-string params like *blocked_dates) is written through as-is.
 * @since 4.4.6
 */
function pewc_portable_array_params() {
	return array(
		'field_options', 'field_rows', 'field_cl_options',
		'child_products', 'child_categories', 'weekdays',
		'condition_field', 'condition_rule', 'condition_value', 'variation_field',
	);
}

/**
 * Group meta keys that are part of a group's portable definition.
 * @since 4.4.6
 */
function pewc_portable_group_meta_keys() {
	return array(
		'group_title', 'group_description', 'group_layout', 'group_class',
		'always_include', 'condition_action', 'condition_match', 'conditions',
		'repeatable', 'repeatable_by_quantity', 'repeatable_limit',
	);
}

/* -------------------------------------------------------------------------
 * Export
 * ---------------------------------------------------------------------- */

/**
 * Build the portable export document for a product.
 * @since 4.4.6
 * @param int $product_id
 * @param int  $product_id
 * @param bool $include_children  Also serialise products referenced by Products /
 *                                Product Categories fields (simple products only).
 * @return array|WP_Error
 */
function pewc_portable_export_build( $product_id, $include_children = false ) {

	$product_id = absint( $product_id );
	if( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
		return new WP_Error( 'pewc_portable_not_product', __( 'Not a valid product.', 'pewc' ) );
	}

	$group_order = pewc_get_group_order( $product_id );
	$group_ids   = $group_order ? array_filter( array_map( 'absint', explode( ',', $group_order ) ) ) : array();

	// Build the old-ID -> ref maps up front so we can rewrite cross-references.
	$group_ref = array(); // old_group_id => "g0"
	$field_ref = array(); // old_field_id => "g0f1"
	$g = 0;
	foreach( $group_ids as $group_id ) {
		$group_ref[ $group_id ] = 'g' . $g;
		$fields = get_post_meta( $group_id, 'field_ids', true );
		$f = 0;
		if( is_array( $fields ) ) {
			foreach( $fields as $field_id ) {
				$field_ref[ absint( $field_id ) ] = 'g' . $g . 'f' . $f;
				$f++;
			}
		}
		$g++;
	}

	$notes = array(
		'dropped_product_refs'   => array(),
		'dropped_variation_refs' => array(),
		'dropped_field_refs'     => array(),
		'dropped_category_refs'  => array(),
		'dropped_uploads'        => array(),
		'uses_global_groups'     => pewc_portable_product_uses_global_groups( $product_id ),
	);

	$groups_out = array();
	$unportable = pewc_portable_unportable_params();
	$attach     = pewc_portable_attachment_params();

	// Populated when $include_children is on and a Products field is found.
	$child_products_map = array(); // key => [ 'match' => [...], 'product' => [...] ]
	$categories_map     = array(); // slug => name

	foreach( $group_ids as $group_id ) {

		$ref  = $group_ref[ $group_id ];
		$meta = array();
		foreach( pewc_portable_group_meta_keys() as $key ) {
			$value = get_post_meta( $group_id, $key, true );
			if( '' !== $value && array() !== $value && null !== $value ) {
				$meta[ $key ] = $value;
			}
		}

		// Rewrite group conditions field refs (pewc_group_G_F) to ref tokens.
		if( ! empty( $meta['conditions'] ) && is_array( $meta['conditions'] ) ) {
			foreach( $meta['conditions'] as $i => $condition ) {
				if( empty( $condition['field'] ) ) {
					continue;
				}
				$token = pewc_portable_id_string_to_ref( $condition['field'], $group_ref, $field_ref );
				if( null === $token ) {
					// The referenced field isn't in this export - drop the condition.
					unset( $meta['conditions'][ $i ] );
				} else {
					$meta['conditions'][ $i ]['field'] = $token;
				}
			}
			$meta['conditions'] = array_values( $meta['conditions'] );
		}

		$fields_out = array();
		$field_objs = pewc_get_group_fields( $group_id );

		foreach( $field_objs as $field_id => $field_obj ) {

			$field_id = absint( $field_id );
			$fref     = isset( $field_ref[ $field_id ] ) ? $field_ref[ $field_id ] : $ref . 'f' . count( $fields_out );

			// Prefer the authoritative all_params blob, fall back to the item object.
			$params = get_post_meta( $field_id, 'all_params', true );
			if( ! is_array( $params ) || empty( $params ) ) {
				$params = $field_obj;
			}
			unset( $params['field_id'], $params['id'], $params['group_id'] );

			$media                  = array();
			$field_out_default_keys = array();

			// Field-level attachment params.
			foreach( $attach as $param ) {
				if( ! empty( $params[ $param ] ) && is_numeric( $params[ $param ] ) ) {
					$img = pewc_portable_attachment_to_media( $params[ $param ] );
					if( $img ) {
						$media[ $param ] = $img;
					}
					$params[ $param ] = '';
				}
			}

			// Option images (image_swatch / select / radio etc).
			if( ! empty( $params['field_options'] ) && is_array( $params['field_options'] ) ) {
				foreach( $params['field_options'] as $oi => $option ) {
					foreach( array( 'image', 'image_alt' ) as $ok ) {
						if( ! empty( $option[ $ok ] ) && is_numeric( $option[ $ok ] ) ) {
							$img = pewc_portable_attachment_to_media( $option[ $ok ] );
							if( $img ) {
								$media['field_options'][ $oi ][ $ok ] = $img;
							}
							$params['field_options'][ $oi ][ $ok ] = '';
						}
					}
				}
			}

			// Information field row images.
			if( ! empty( $params['field_rows'] ) && is_array( $params['field_rows'] ) ) {
				foreach( $params['field_rows'] as $ri => $row ) {
					if( ! empty( $row['image'] ) && is_numeric( $row['image'] ) ) {
						$img = pewc_portable_attachment_to_media( $row['image'] );
						if( $img ) {
							$media['field_rows'][ $ri ]['image'] = $img;
						}
						$params['field_rows'][ $ri ]['image'] = '';
					}
				}
			}

			// Cross-reference params we CAN remap: condition_field + formula.
			if( ! empty( $params['condition_field'] ) && is_array( $params['condition_field'] ) ) {
				foreach( $params['condition_field'] as $ci => $cf ) {
					$token = pewc_portable_id_string_to_ref( $cf, $group_ref, $field_ref );
					$params['condition_field'][ $ci ] = ( null === $token ) ? '' : $token;
				}
			}
			if( ! empty( $params['formula'] ) && is_string( $params['formula'] ) ) {
				$params['formula'] = pewc_portable_formula_to_refs( $params['formula'], $field_ref );
			}

			$field_type = isset( $params['field_type'] ) ? $params['field_type'] : '';

			// Products / Product Categories fields: optionally carry the referenced
			// child products, and always record referenced category slugs+names.
			if( 'products' === $field_type || 'product-categories' === $field_type ) {

				// child_categories are slugs - portable on their own. Record names too.
				if( ! empty( $params['child_categories'] ) && is_array( $params['child_categories'] ) ) {
					foreach( $params['child_categories'] as $slug ) {
						if( '' === $slug || isset( $categories_map[ $slug ] ) ) {
							continue;
						}
						$term = get_term_by( 'slug', $slug, 'product_cat' );
						$categories_map[ $slug ] = ( $term && ! is_wp_error( $term ) ) ? $term->name : $slug;
					}
				}

				if( ! empty( $params['child_products'] ) && is_array( $params['child_products'] ) ) {

					$new_child_refs = array();
					foreach( $params['child_products'] as $child_id ) {

						$child_id = absint( $child_id );
						$child    = $child_id ? wc_get_product( $child_id ) : false;

						if( ! $child || ! $child->is_type( 'simple' ) ) {
							// Variable / variation / missing - can't travel.
							$notes['dropped_product_refs'][] = array(
								'group' => $ref, 'field' => $fref,
								'label' => isset( $params['field_label'] ) ? $params['field_label'] : '',
								'param' => 'child_products', 'value' => $child_id,
							);
							continue;
						}

						if( ! $include_children ) {
							$notes['dropped_product_refs'][] = array(
								'group' => $ref, 'field' => $fref,
								'label' => isset( $params['field_label'] ) ? $params['field_label'] : '',
								'param' => 'child_products', 'value' => $child_id,
							);
							continue;
						}

						$key = pewc_portable_child_key( $child );
						if( ! isset( $child_products_map[ $key ] ) ) {
							$child_data = pewc_portable_child_product_data( $child );
							if( ! $child_data ) {
								continue;
							}
							$child_products_map[ $key ] = $child_data;
						}
						$new_child_refs[] = $key;

						// Fold the child's own categories into the map.
						foreach( $child->get_category_ids() as $cat_id ) {
							$term = get_term( $cat_id, 'product_cat' );
							if( $term && ! is_wp_error( $term ) && ! isset( $categories_map[ $term->slug ] ) ) {
								$categories_map[ $term->slug ] = $term->name;
							}
						}
					}

					if( $include_children ) {
						$params['child_products'] = $new_child_refs;
						// On a Products field, field_default is a CSV of default child IDs.
						if( 'products' === $field_type && ! empty( $params['field_default'] ) && function_exists( 'pewc_get_default_child_products' ) ) {
							$default_keys = array();
							foreach( pewc_get_default_child_products( $params['field_default'] ) as $def_id ) {
								$def = wc_get_product( $def_id );
								if( $def && $def->is_type( 'simple' ) ) {
									$default_keys[] = pewc_portable_child_key( $def );
								}
							}
							if( $default_keys ) {
								$field_out_default_keys = $default_keys;
								$params['field_default'] = '';
							}
						}
					}
				}
			}

			// Params that cannot travel - record and blank. (child_products is
			// already handled above for Products fields.)
			foreach( $unportable as $param => $bucket ) {
				if( 'child_products' === $param && ( 'products' === $field_type || 'product-categories' === $field_type ) ) {
					continue;
				}
				if( 'child_categories' === $param ) {
					continue; // slugs travel as-is
				}
				if( isset( $params[ $param ] ) && '' !== $params[ $param ] && array() !== $params[ $param ] ) {
					$notes[ $bucket ][] = array(
						'group' => $ref,
						'field' => $fref,
						'label' => isset( $params['field_label'] ) ? $params['field_label'] : '',
						'param' => $param,
						'value' => $params[ $param ],
					);
					$params[ $param ] = is_array( $params[ $param ] ) ? array() : '';
				}
			}

			// Upload-field default file.
			if( isset( $params['field_type'] ) && 'upload' === $params['field_type'] ) {
				foreach( array( 'field_default', 'field_default_hidden' ) as $param ) {
					if( ! empty( $params[ $param ] ) ) {
						$notes['dropped_uploads'][] = array(
							'group' => $ref, 'field' => $fref, 'param' => $param, 'value' => $params[ $param ],
						);
						$params[ $param ] = '';
					}
				}
			}

			$field_out = array(
				'ref'    => $fref,
				'params' => $params,
			);
			if( $media ) {
				$field_out['media'] = $media;
			}
			if( $field_out_default_keys ) {
				$field_out['child_default_keys'] = $field_out_default_keys;
			}
			$fields_out[] = $field_out;

		}

		$groups_out[] = array(
			'ref'    => $ref,
			'meta'   => $meta,
			'fields' => $fields_out,
		);

	}

	$product = wc_get_product( $product_id );
	$title   = $product ? $product->get_name() : get_the_title( $product_id );

	$data = array(
		'format'         => PEWC_PORTABLE_FORMAT,
		'format_version' => PEWC_PORTABLE_FORMAT_VERSION,
		'label'          => $title,
		'plugin_version' => PEWC_PLUGIN_VERSION,
		'exported_from'  => home_url(),
		'exported_at'    => gmdate( 'c' ),
		'source_product' => array(
			'id'    => $product_id,
			'title' => $title,
			'sku'   => $product ? $product->get_sku() : '',
		),
		'groups'         => $groups_out,
		'notes'          => $notes,
	);

	if( $categories_map ) {
		$data['categories'] = $categories_map;
	}
	if( $child_products_map ) {
		$data['child_products'] = $child_products_map;
	}

	return apply_filters( 'pewc_portable_export_data', $data, $product_id );

}

/**
 * Does THIS product actually have global groups applied (manually assigned, or
 * matched by a global group's rules)? Informational only - global groups are
 * not part of the export.
 * @since 4.4.6
 */
function pewc_portable_product_uses_global_groups( $product_id ) {

	// Manual assignment on the product.
	$manual = get_post_meta( $product_id, 'pewc_global_groups_by_product', true );
	if( ! empty( $manual ) ) {
		return true;
	}

	// No global groups at all on the site.
	if( empty( get_option( 'pewc_global_group_order', '' ) ) ) {
		return false;
	}

	// Rule-based: ask the same filter the front end uses which globals apply to
	// this product. It starts from the passed array and adds matching globals,
	// so a non-empty result means at least one global group targets this product.
	if( function_exists( 'pewc_filter_product_extra_groups' ) ) {
		$applied = pewc_filter_product_extra_groups( array(), $product_id );
		return is_array( $applied ) && ! empty( $applied );
	}

	return false;
}

/**
 * Whether add-on exports should embed image bytes (base64) in the file.
 * Controlled by the "Embed images in add-on exports" setting; filterable.
 * @since 4.4.6
 */
function pewc_portable_embed_images() {
	$enabled = 'yes' === get_option( 'pewc_export_embed_images', 'no' );
	return (bool) apply_filters( 'pewc_portable_embed_images', $enabled );
}

/**
 * Largest image file (bytes) to embed directly in the export as base64.
 * Larger images fall back to URL-only. Filterable.
 * @since 4.4.6
 */
function pewc_portable_max_embed_bytes() {
	return (int) apply_filters( 'pewc_portable_max_embed_bytes', 2 * MB_IN_BYTES );
}

/**
 * Turn an attachment ID into a portable media descriptor. When the "Embed images
 * in add-on exports" setting is on, the image bytes are embedded as base64
 * (`data`) so import works with no outbound HTTP. `url` is always included.
 * @since 4.4.6
 * @return array|false
 */
function pewc_portable_attachment_to_media( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	if( ! $attachment_id ) {
		return false;
	}
	$url = wp_get_attachment_url( $attachment_id );
	if( ! $url ) {
		return false;
	}

	$media = array(
		'url'      => $url,
		'alt'      => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
		'filename' => wp_basename( parse_url( $url, PHP_URL_PATH ) ),
	);

	if( ! pewc_portable_embed_images() ) {
		return $media;
	}

	$path = get_attached_file( $attachment_id );
	if( $path && is_readable( $path ) ) {
		$size = filesize( $path );
		if( $size && $size <= pewc_portable_max_embed_bytes() ) {
			$bytes = file_get_contents( $path );
			if( false !== $bytes ) {
				$type = get_post_mime_type( $attachment_id );
				if( ! $type ) {
					$ft   = wp_check_filetype( $path );
					$type = ! empty( $ft['type'] ) ? $ft['type'] : 'application/octet-stream';
				}
				$media['mime'] = $type;
				$media['data'] = base64_encode( $bytes );
				if( empty( $media['filename'] ) ) {
					$media['filename'] = wp_basename( $path );
				}
			}
		}
	}

	return $media;
}

/* -------------------------------------------------------------------------
 * Child products (Products / Product Categories fields)
 * ---------------------------------------------------------------------- */

/**
 * Does any field in these groups reference other products (Products or
 * Product Categories field type)? Determines whether to offer the export prompt.
 * @since 4.4.6
 */
function pewc_portable_field_set_has_products( $group_ids ) {
	foreach( (array) $group_ids as $group_id ) {
		$field_ids = get_post_meta( $group_id, 'field_ids', true );
		if( ! is_array( $field_ids ) ) {
			continue;
		}
		foreach( $field_ids as $field_id ) {
			$type = get_post_meta( $field_id, 'field_type', true );
			if( ! $type ) {
				$all = get_post_meta( $field_id, 'all_params', true );
				$type = is_array( $all ) && isset( $all['field_type'] ) ? $all['field_type'] : '';
			}
			if( 'products' === $type || 'product-categories' === $type ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Stable key for a child product in the export: its SKU, else "id:<id>".
 * @since 4.4.6
 */
function pewc_portable_child_key( $product ) {
	$sku = $product->get_sku();
	return $sku ? 'sku:' . $sku : 'id:' . $product->get_id();
}

/**
 * Serialise a simple product so it can be matched or recreated on another site.
 * @since 4.4.6
 * @param WC_Product $product
 * @return array|null  null if not a simple product.
 */
function pewc_portable_child_product_data( $product ) {

	if( ! $product || ! $product->is_type( 'simple' ) ) {
		return null;
	}

	$id = $product->get_id();

	$category_slugs = array();
	foreach( $product->get_category_ids() as $cat_id ) {
		$term = get_term( $cat_id, 'product_cat' );
		if( $term && ! is_wp_error( $term ) ) {
			$category_slugs[] = $term->slug;
		}
	}

	$image = false;
	if( $product->get_image_id() ) {
		$image = pewc_portable_attachment_to_media( $product->get_image_id() );
	}
	$gallery = array();
	foreach( $product->get_gallery_image_ids() as $gid ) {
		$g = pewc_portable_attachment_to_media( $gid );
		if( $g ) {
			$gallery[] = $g;
		}
	}

	$entry = array(
		'match' => array(
			'sku'   => $product->get_sku(),
			'title' => $product->get_name(),
		),
		'product' => array(
			'title'              => $product->get_name(),
			'status'             => $product->get_status(),
			'catalog_visibility' => $product->get_catalog_visibility(),
			'sku'                => $product->get_sku(),
			'regular_price'      => $product->get_regular_price(),
			'sale_price'         => $product->get_sale_price(),
			'date_on_sale_from'  => $product->get_date_on_sale_from() ? $product->get_date_on_sale_from()->date( 'Y-m-d H:i:s' ) : '',
			'date_on_sale_to'    => $product->get_date_on_sale_to() ? $product->get_date_on_sale_to()->date( 'Y-m-d H:i:s' ) : '',
			'short_description'  => $product->get_short_description(),
			'description'        => $product->get_description(),
			'tax_status'         => $product->get_tax_status(),
			'tax_class'          => $product->get_tax_class(),
			'manage_stock'       => $product->get_manage_stock(),
			'stock_quantity'     => $product->get_stock_quantity(),
			'stock_status'       => $product->get_stock_status(),
			'backorders'         => $product->get_backorders(),
			'sold_individually'  => $product->get_sold_individually(),
			'virtual'            => $product->get_virtual(),
			'downloadable'       => $product->get_downloadable(),
			'weight'             => $product->get_weight(),
			'length'             => $product->get_length(),
			'width'              => $product->get_width(),
			'height'             => $product->get_height(),
			'shipping_class'     => $product->get_shipping_class(),
			'menu_order'         => $product->get_menu_order(),
			'category_slugs'     => $category_slugs,
		),
	);

	if( $image ) {
		$entry['product']['image'] = $image;
	}
	if( $gallery ) {
		$entry['product']['gallery'] = $gallery;
	}

	return apply_filters( 'pewc_portable_child_product_data', $entry, $product );
}

/**
 * Convert a "pewc_group_<gid>_<fid>" id string to a "ref:g0f1" token.
 * Returns null if the referenced field is not part of this export.
 * @since 4.4.6
 */
function pewc_portable_id_string_to_ref( $id_string, $group_ref, $field_ref ) {
	if( ! is_string( $id_string ) || false === strpos( $id_string, 'pewc_group_' ) ) {
		return $id_string; // not an id string - leave alone (e.g. "not-selected")
	}
	$field_id = absint( pewc_get_field_id( $id_string ) );
	if( isset( $field_ref[ $field_id ] ) ) {
		return 'ref:' . $field_ref[ $field_id ];
	}
	return null;
}

/**
 * Rewrite {field_<id>} tokens in a formula to {field_ref:g0f1}.
 * @since 4.4.6
 */
function pewc_portable_formula_to_refs( $formula, $field_ref ) {
	foreach( $field_ref as $old_id => $ref ) {
		$formula = str_replace( '{field_' . $old_id, '{field_ref:' . $ref, $formula );
	}
	return $formula;
}

/* -------------------------------------------------------------------------
 * Export - menu page + download handler + product-screen button
 * ---------------------------------------------------------------------- */

/**
 * Register hidden submenu pages for the export/import tools.
 * @since 4.4.6
 */
function pewc_portable_register_pages() {
	if( ! pewc_portable_file_transfer_enabled() ) {
		return;
	}
	add_submenu_page( 'pewc_home', __( 'Export Add-Ons', 'pewc' ), __( 'Export Add-Ons', 'pewc' ), pewc_portable_capability(), 'pewc-export-addons', 'pewc_portable_export_page' );
	add_submenu_page( 'pewc_home', __( 'Import Add-Ons', 'pewc' ), __( 'Import Add-Ons', 'pewc' ), pewc_portable_capability(), 'pewc-import-addons', 'pewc_portable_import_page' );
}
add_action( 'admin_menu', 'pewc_portable_register_pages', 199 );

/**
 * Hide the tool pages from the menu (kept reachable by URL, parent stays highlighted).
 * @since 4.4.6
 */
function pewc_portable_remove_menus() {
	remove_submenu_page( 'pewc_home', 'pewc-export-addons' );
	remove_submenu_page( 'pewc_home', 'pewc-import-addons' );
}
add_action( 'admin_head', 'pewc_portable_remove_menus' );

/**
 * Stream the JSON download when ?page=pewc-export-addons&pewc_download_addons=1.
 * @since 4.4.6
 */
function pewc_portable_maybe_download() {

	// NB: use our own query var, not "do_export" - that one is claimed by the
	// legacy order-CSV export in admin/functions-admin-export.php, which is
	// required earlier and would die() before we run.
	if( empty( $_GET['page'] ) || 'pewc-export-addons' !== $_GET['page'] || empty( $_GET['pewc_download_addons'] ) ) {
		return;
	}

	if( ! pewc_portable_file_transfer_enabled() ) {
		wp_die( esc_html__( 'File import/export is not enabled.', 'pewc' ) );
	}
	if( ! current_user_can( pewc_portable_capability() ) ) {
		wp_die( esc_html__( 'You do not have permission to export add-ons.', 'pewc' ) );
	}
	if( ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '', 'pewc_portable_export' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'pewc' ) );
	}
	$product_id       = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
	$include_children = ! empty( $_GET['include_children'] );

	$data = pewc_portable_export_build( $product_id, $include_children );
	if( is_wp_error( $data ) ) {
		wp_die( esc_html( $data->get_error_message() ) );
	}

	$slug     = $data['source_product']['sku'] ? $data['source_product']['sku'] : $product_id;
	$filename = 'pewc-addons-' . sanitize_file_name( $slug ) . '-' . gmdate( 'Ymd' ) . '.json';

	nocache_headers();
	while( ob_get_level() ) {
		ob_end_clean();
	}
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	echo wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	exit;

}
add_action( 'admin_init', 'pewc_portable_maybe_download' );

/**
 * AJAX: does this product have Products / Product Categories add-on fields?
 * Used to decide whether to prompt about exporting child products.
 * @since 4.4.6
 */
function pewc_portable_export_check() {

	if( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'pewc_portable_export_check' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'pewc' ) ) );
	}
	if( ! pewc_portable_file_transfer_enabled() ) {
		wp_send_json_error( array( 'message' => __( 'File import/export is not enabled.', 'pewc' ) ) );
	}
	if( ! current_user_can( pewc_portable_capability() ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission.', 'pewc' ) ) );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Not a valid product.', 'pewc' ) ) );
	}

	$group_order = pewc_get_group_order( $product_id );
	$group_ids   = $group_order ? array_filter( array_map( 'absint', explode( ',', $group_order ) ) ) : array();

	$preview = pewc_portable_child_products_preview( $group_ids );

	// Build both variants so the front end can show the right notice depending
	// on whether the user chooses to include child products.
	$without = pewc_portable_export_build( $product_id, false );
	$with    = pewc_portable_export_build( $product_id, true );

	wp_send_json_success( array(
		'has_products'    => pewc_portable_field_set_has_products( $group_ids ),
		'simple_count'    => $preview['simple'],
		'nonsimple_count' => $preview['nonsimple'],
		'notes_without'   => is_wp_error( $without ) ? array() : pewc_portable_summarise_notes( $without['notes'] ),
		'notes_with'      => is_wp_error( $with ) ? array() : pewc_portable_summarise_notes( $with['notes'] ),
	) );

}
add_action( 'wp_ajax_pewc_portable_export_check', 'pewc_portable_export_check' );

/**
 * Turn an export's raw "notes" into human-readable warning lines for the
 * post-export confirmation notice. Shares wording with the import report.
 * @since 4.4.6
 */
function pewc_portable_summarise_notes( $notes ) {

	$lines = array();

	if( ! empty( $notes['dropped_product_refs'] ) ) {
		$n = count( $notes['dropped_product_refs'] );
		$lines[] = sprintf(
			_n(
				'%d product reference could not be included and must be set up manually on the destination site.',
				'%d product references could not be included and must be set up manually on the destination site.',
				$n, 'pewc'
			), $n
		);
	}
	if( ! empty( $notes['dropped_variation_refs'] ) ) {
		$n = count( $notes['dropped_variation_refs'] );
		$lines[] = sprintf(
			_n(
				'%d variation-specific setting was removed (the field will show for all variations after import).',
				'%d variation-specific settings were removed (the fields will show for all variations after import).',
				$n, 'pewc'
			), $n
		);
	}
	if( ! empty( $notes['dropped_field_refs'] ) ) {
		$n = count( $notes['dropped_field_refs'] );
		$lines[] = sprintf(
			_n(
				'%d field-to-field reference could not be transferred.',
				'%d field-to-field references could not be transferred.',
				$n, 'pewc'
			), $n
		);
	}
	if( ! empty( $notes['dropped_uploads'] ) ) {
		$n = count( $notes['dropped_uploads'] );
		$lines[] = sprintf(
			_n(
				'%d default uploaded file was removed.',
				'%d default uploaded files were removed.',
				$n, 'pewc'
			), $n
		);
	}

	if( ! empty( $notes['uses_global_groups'] ) ) {
		$lines[] = __( 'This product also uses global groups. Global groups are not included - assign them separately on the destination site.', 'pewc' );
	}

	if( ! pewc_portable_embed_images() ) {
		$lines[] = __( 'Images are referenced by URL. The destination site must be able to reach this site to download them, or turn on "Embed images in add-on exports" in the plugin settings.', 'pewc' );
	}

	return $lines;
}

/**
 * Count simple vs non-simple (variable / variation / missing) products referenced
 * by Products fields in these groups. Used to warn the user before export.
 * @since 4.4.6
 * @return array { simple: int, nonsimple: int }
 */
function pewc_portable_child_products_preview( $group_ids ) {
	$simple = 0;
	$nonsimple = 0;
	$seen = array();
	foreach( (array) $group_ids as $group_id ) {
		$field_ids = get_post_meta( $group_id, 'field_ids', true );
		if( ! is_array( $field_ids ) ) {
			continue;
		}
		foreach( $field_ids as $field_id ) {
			$all  = get_post_meta( $field_id, 'all_params', true );
			$type = is_array( $all ) && isset( $all['field_type'] ) ? $all['field_type'] : get_post_meta( $field_id, 'field_type', true );
			if( 'products' !== $type && 'product-categories' !== $type ) {
				continue;
			}
			$children = is_array( $all ) && ! empty( $all['child_products'] ) ? $all['child_products'] : get_post_meta( $field_id, 'child_products', true );
			if( ! is_array( $children ) ) {
				continue;
			}
			foreach( $children as $cid ) {
				$cid = absint( $cid );
				if( ! $cid || isset( $seen[ $cid ] ) ) {
					continue;
				}
				$seen[ $cid ] = true;
				$child = wc_get_product( $cid );
				if( $child && $child->is_type( 'simple' ) ) {
					$simple++;
				} else {
					$nonsimple++;
				}
			}
		}
	}
	return array( 'simple' => $simple, 'nonsimple' => $nonsimple );
}

/**
 * Export tool page - lets the user pick a product and download.
 * @since 4.4.6
 */
function pewc_portable_export_page() {
	$preselect = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
	?>
	<div class="wrap pewc-portable-wrap">
		<h1><?php esc_html_e( 'Export Add-Ons to File', 'pewc' ); ?></h1>
		<p><?php esc_html_e( 'Choose a product and download its add-on groups and fields as a JSON file. You can import this file onto a product on this or another site.', 'pewc' ); ?></p>
		<p>
			<select class="wc-product-search" id="pewc_portable_export_product" style="min-width:320px;" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'pewc' ); ?>" data-action="woocommerce_json_search_products">
				<?php if( $preselect ) {
					$p = wc_get_product( $preselect );
					if( $p ) {
						printf( '<option value="%d" selected>%s</option>', esc_attr( $preselect ), esc_html( $p->get_formatted_name() ) );
					}
				} ?>
			</select>
		</p>
		<?php wp_nonce_field( 'pewc_portable_export_check', 'pewc_portable_export_check_nonce' ); ?>
		<p>
			<button type="button" class="button button-primary" id="pewc_portable_export_go" data-download-base="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=pewc-export-addons&pewc_download_addons=1' ), 'pewc_portable_export' ) ); ?>"><?php esc_html_e( 'Download export file', 'pewc' ); ?></button>
		</p>
		<div class="pewc-portable-export-result" hidden></div>
		<p class="description">
			<?php
			if( pewc_portable_embed_images() ) {
				esc_html_e( 'Images are embedded in the file so it can be imported on any site.', 'pewc' );
			} else {
				esc_html_e( 'Images are referenced by URL and downloaded on import - the destination site must be able to reach this site. Turn on "Embed images in add-on exports" in the plugin settings to include the image data in the file instead.', 'pewc' );
			}
			?>
			<?php esc_html_e( 'If a Products or Product Categories field is included, you can choose to export the simple products it references too. Variable products and product variations are never included.', 'pewc' ); ?>
		</p>
	</div>
	<?php
}

/**
 * "Install Add-Ons from Library" section on the product Add-Ons panel.
 * Rendered before the same-site "Import/Export Add-Ons" section (priority 10).
 * @since 4.4.6
 */
function pewc_portable_library_section( $groups, $post_id ) {

	if( ! pewc_portable_library_enabled() ) {
		return;
	}

	$demos = pewc_portable_get_demo_products();
	if( ! $demos ) {
		return;
	}

	echo '<div class="options_group pewc-group-settings pewc-portable-library">';
	printf(
		'<p><strong>%s</strong></p><p>%s</p>',
		esc_html__( 'Install Add-Ons from Library', 'pewc' ),
		esc_html__( 'Add a ready-made set of add-on fields to this product from one of the demo products.', 'pewc' )
	);
	echo '<p class="pewc-portable-demo-row">';
	printf( '<label for="pewc_demo_product_select" class="screen-reader-text">%s</label> ', esc_html__( 'Demo product', 'pewc' ) );
	echo '<select id="pewc_demo_product_select">';
	printf( '<option value="">%s</option>', esc_html__( '— Select a demo product —', 'pewc' ) );
	foreach( $demos as $demo ) {
		printf( '<option value="%s">%s</option>', esc_attr( $demo['slug'] ), esc_html( $demo['label'] ) );
	}
	echo '</select> ';
	printf(
		'<button type="button" class="button" id="pewc_create_from_demo" data-product-id="%d">%s</button>',
		esc_attr( $post_id ),
		esc_html__( 'Create add-ons', 'pewc' )
	);
	echo ' <span class="pewc-portable-demo-status" aria-live="polite"></span>';
	echo '</p>';
	echo '</div>';

}
add_action( 'pewc_end_tab_options', 'pewc_portable_library_section', 8, 2 );

/**
 * "Transfer Add-Ons Between Sites" (file import/export) section on the product
 * Add-Ons panel. Only shown when file import/export is enabled.
 * @since 4.4.6
 */
function pewc_portable_product_buttons( $groups, $post_id ) {

	if( ! pewc_portable_file_transfer_enabled() ) {
		return;
	}

	$export_url = wp_nonce_url(
		admin_url( 'admin.php?page=pewc-export-addons&pewc_download_addons=1&product_id=' . $post_id ),
		'pewc_portable_export'
	);
	wp_nonce_field( 'pewc_portable_export_check', 'pewc_portable_export_check_nonce' );

	echo '<div class="options_group pewc-group-settings pewc-portable-buttons">';
	printf(
		'<p><strong>%s</strong></p><p>%s</p><p class="pewc-portable-buttons-row">
			<button type="button" class="button" id="pewc_portable_export_open" data-product-id="%d" data-download-base="%s">%s</button>
			<button type="button" class="button" id="pewc_portable_import_open" data-product-id="%d">%s</button>
		</p>
		<div class="pewc-portable-export-result" hidden></div>',
		esc_html__( 'Transfer Add-Ons Between Sites', 'pewc' ),
		esc_html__( 'Export this product\'s groups and fields to a file, or import a file exported from a product on this or another site.', 'pewc' ),
		esc_attr( $post_id ),
		esc_url( $export_url ),
		esc_html__( 'Export to file', 'pewc' ),
		esc_attr( $post_id ),
		esc_html__( 'Import from file', 'pewc' )
	);
	echo '</div>';

	// Import modal (hidden until "Import from file" is clicked).
	wp_nonce_field( 'pewc_portable_import', 'pewc_portable_import_nonce_panel' );
	?>
	<div id="pewc_portable_import_modal" class="pewc-portable-modal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="pewc_portable_import_modal_title">
		<div class="pewc-portable-modal-backdrop"></div>
		<div class="pewc-portable-modal-box">
			<button type="button" class="pewc-portable-modal-close" aria-label="<?php esc_attr_e( 'Close', 'pewc' ); ?>">&times;</button>
			<h2 id="pewc_portable_import_modal_title"><?php esc_html_e( 'Import Add-Ons from File', 'pewc' ); ?></h2>
			<p><?php esc_html_e( 'Choose a JSON file exported from a product. Its groups and fields will be added to this product.', 'pewc' ); ?></p>
			<p>
				<input type="file" id="pewc_portable_modal_file" accept="application/json,.json" data-product-id="<?php echo esc_attr( $post_id ); ?>">
			</p>
			<div class="pewc-portable-modal-result" hidden></div>
			<p class="pewc-portable-modal-actions">
				<button type="button" class="button button-primary" id="pewc_portable_modal_import"><?php esc_html_e( 'Import', 'pewc' ); ?></button>
				<button type="button" class="button pewc-portable-modal-close"><?php esc_html_e( 'Cancel', 'pewc' ); ?></button>
			</p>
		</div>
	</div>
	<?php

}
add_action( 'pewc_end_tab_options', 'pewc_portable_product_buttons', 12, 2 );

/* -------------------------------------------------------------------------
 * Demo library
 * ---------------------------------------------------------------------- */

/**
 * Absolute path to the bundled demo-products directory.
 * @since 4.4.6
 */
function pewc_portable_demo_dir() {
	return trailingslashit( PEWC_PLUGIN_DIR_PATH ) . 'demo-products/';
}

/**
 * List the available demo products (bundled files + any registered via filter).
 * @since 4.4.6
 * @return array [ [ 'slug', 'label', 'path', 'groups', 'fields' ], ... ]
 */
function pewc_portable_get_demo_products() {

	$demos = array();
	$files = glob( pewc_portable_demo_dir() . '*.json' );

	if( $files ) {
		foreach( $files as $path ) {
			$data = pewc_portable_read_json_file( $path );
			if( is_wp_error( $data ) ) {
				continue;
			}
			$slug   = sanitize_key( wp_basename( $path, '.json' ) );
			$fields = 0;
			foreach( (array) $data['groups'] as $group ) {
				$fields += isset( $group['fields'] ) ? count( $group['fields'] ) : 0;
			}
			$demos[ $slug ] = array(
				'slug'   => $slug,
				'label'  => ! empty( $data['label'] ) ? $data['label'] : ( ! empty( $data['source_product']['title'] ) ? $data['source_product']['title'] : $slug ),
				'path'   => $path,
				'groups' => count( (array) $data['groups'] ),
				'fields' => $fields,
			);
		}
	}

	/**
	 * Register extra demo products. Each entry must include a real filesystem
	 * 'path' to a JSON file in the portable export format.
	 * @since 4.4.6
	 */
	$demos = apply_filters( 'pewc_portable_demo_products', $demos );

	return $demos;

}

/**
 * Read + validate a portable JSON file from disk.
 * @since 4.4.6
 * @return array|WP_Error
 */
function pewc_portable_read_json_file( $path ) {
	if( ! is_string( $path ) || ! is_readable( $path ) ) {
		return new WP_Error( 'pewc_portable_unreadable', __( 'Could not read the file.', 'pewc' ) );
	}
	$raw = file_get_contents( $path );
	return pewc_portable_parse( $raw );
}

/**
 * Parse + validate a portable JSON string.
 * @since 4.4.6
 * @return array|WP_Error
 */
function pewc_portable_parse( $raw ) {
	$data = json_decode( (string) $raw, true );
	if( ! is_array( $data ) ) {
		return new WP_Error( 'pewc_portable_bad_json', __( 'The file is not valid JSON.', 'pewc' ) );
	}
	if( empty( $data['format'] ) || PEWC_PORTABLE_FORMAT !== $data['format'] ) {
		return new WP_Error( 'pewc_portable_bad_format', __( 'This file is not a Product Add-Ons export.', 'pewc' ) );
	}
	if( ! empty( $data['format_version'] ) && (int) $data['format_version'] > PEWC_PORTABLE_FORMAT_VERSION ) {
		return new WP_Error( 'pewc_portable_newer', __( 'This file was exported from a newer version of the plugin. Please update Product Add-Ons Ultimate.', 'pewc' ) );
	}
	if( empty( $data['groups'] ) || ! is_array( $data['groups'] ) ) {
		return new WP_Error( 'pewc_portable_empty', __( 'This file contains no add-on groups.', 'pewc' ) );
	}
	return $data;
}

/* -------------------------------------------------------------------------
 * Import
 * ---------------------------------------------------------------------- */

/**
 * Import tool page.
 * @since 4.4.6
 */
function pewc_portable_import_page() {
	$preselect = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
	wp_nonce_field( 'pewc_portable_import', 'pewc_portable_import_nonce' );
	?>
	<div class="wrap pewc-portable-wrap">
		<h1><?php esc_html_e( 'Import Add-Ons from File', 'pewc' ); ?></h1>
		<p><?php esc_html_e( 'Select a destination product and an export file. The groups and fields in the file will be added to the product.', 'pewc' ); ?></p>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="pewc_portable_import_product"><?php esc_html_e( 'Destination product', 'pewc' ); ?></label></th>
				<td>
					<select class="wc-product-search" id="pewc_portable_import_product" style="min-width:320px;" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'pewc' ); ?>" data-action="woocommerce_json_search_products">
						<?php if( $preselect ) {
							$p = wc_get_product( $preselect );
							if( $p ) {
								printf( '<option value="%d" selected>%s</option>', esc_attr( $preselect ), esc_html( $p->get_formatted_name() ) );
							}
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="pewc_portable_import_file"><?php esc_html_e( 'Export file', 'pewc' ); ?></label></th>
				<td><input type="file" id="pewc_portable_import_file" accept="application/json,.json"></td>
			</tr>
		</table>

		<p>
			<button type="button" class="button button-primary" id="pewc_portable_import_go"><?php esc_html_e( 'Import add-ons', 'pewc' ); ?></button>
			<span class="spinner" style="float:none;"></span>
		</p>

		<div id="pewc_portable_import_result" hidden></div>
	</div>
	<?php
}

/**
 * AJAX: run an import from an uploaded file or a bundled demo product.
 * @since 4.4.6
 */
function pewc_portable_import_ajax() {

	if( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'pewc_portable_import' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'pewc' ) ) );
	}
	if( ! current_user_can( pewc_portable_capability() ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to import add-ons.', 'pewc' ) ) );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Please choose a valid destination product.', 'pewc' ) ) );
	}

	$source = isset( $_POST['source'] ) ? sanitize_key( $_POST['source'] ) : 'file';

	if( 'demo' === $source ) {
		if( ! pewc_portable_library_enabled() ) {
			wp_send_json_error( array( 'message' => __( 'The add-ons library is not enabled.', 'pewc' ) ) );
		}
		$slug  = isset( $_POST['demo_slug'] ) ? sanitize_key( $_POST['demo_slug'] ) : '';
		$demos = pewc_portable_get_demo_products();
		if( ! $slug || ! isset( $demos[ $slug ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown demo product.', 'pewc' ) ) );
		}
		$data = pewc_portable_read_json_file( $demos[ $slug ]['path'] );
	} else {
		if( ! pewc_portable_file_transfer_enabled() ) {
			wp_send_json_error( array( 'message' => __( 'File import/export is not enabled.', 'pewc' ) ) );
		}
		if( empty( $_FILES['import_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['import_file']['tmp_name'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Please select an export file to upload.', 'pewc' ) ) );
		}
		$data = pewc_portable_parse( file_get_contents( $_FILES['import_file']['tmp_name'] ) );
	}

	if( is_wp_error( $data ) ) {
		wp_send_json_error( array( 'message' => $data->get_error_message() ) );
	}

	// Two-step confirmation: if the file carries child products and the caller
	// hasn't answered yet, ask before creating anything.
	if( ! empty( $data['child_products'] ) && ! isset( $_POST['create_children'] ) ) {
		$skus = array();
		foreach( $data['child_products'] as $entry ) {
			$skus[] = isset( $entry['match']['title'] ) && $entry['match']['title'] !== ''
				? $entry['match']['title']
				: ( isset( $entry['match']['sku'] ) ? $entry['match']['sku'] : '' );
		}
		wp_send_json_success( array(
			'needs_confirm' => 'children',
			'count'         => count( $data['child_products'] ),
			'names'         => array_values( array_filter( $skus ) ),
		) );
	}

	$create_children = isset( $_POST['create_children'] ) && '1' === (string) $_POST['create_children'];

	$result = pewc_portable_import_apply( $product_id, $data, $create_children );

	if( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success( $result );

}
add_action( 'wp_ajax_pewc_portable_import', 'pewc_portable_import_ajax' );

/**
 * Create groups + fields from a parsed portable document and attach them to a product.
 * @since 4.4.6
 * @param int   $product_id
 * @param array $data             Parsed + validated portable document.
 * @param bool  $create_children  Create child products that aren't matched by SKU/name.
 * @return array|WP_Error  Report on success.
 */
function pewc_portable_import_apply( $product_id, $data, $create_children = false ) {

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$field_params = pewc_get_field_params();
	$ref_to_group = array(); // "g0"   => new_group_id
	$ref_to_field = array(); // "g0f1" => new_field_id
	$image_cache  = array(); // url    => attachment_id|0
	$report       = array();

	// --- Recreate referenced product categories (slugs) ---
	if( ! empty( $data['categories'] ) && is_array( $data['categories'] ) ) {
		pewc_portable_recreate_categories( $data['categories'] );
	}

	// --- Resolve / create child products, build key -> product ID map ---
	$child_key_map = array(); // export key => product ID on this site
	if( ! empty( $data['child_products'] ) && is_array( $data['child_products'] ) ) {
		$matched = array();
		$created = array();
		foreach( $data['child_products'] as $key => $entry ) {
			$pid = pewc_portable_resolve_child_product( $entry, $create_children, $matched, $created );
			if( $pid ) {
				$child_key_map[ $key ] = $pid;
			}
		}
		if( $matched ) {
			$report[] = sprintf(
				/* translators: %s: comma-separated product names */
				__( 'Linked %1$d child product(s) to existing products by SKU or name (existing products were not modified - check their prices and images if the source differs): %2$s', 'pewc' ),
				count( $matched ),
				implode( ', ', $matched )
			);
		}
		if( $created ) {
			$report[] = sprintf(
				/* translators: %s: comma-separated product names */
				__( 'Created %1$d child product(s): %2$s', 'pewc' ),
				count( $created ),
				implode( ', ', $created )
			);
		}
		$unresolved = count( $data['child_products'] ) - count( $child_key_map );
		if( $unresolved > 0 ) {
			$report[] = sprintf(
				_n( '%d referenced child product could not be linked and was skipped.', '%d referenced child products could not be linked and were skipped.', $unresolved, 'pewc' ),
				$unresolved
			);
		}
	}

	$new_group_ids = array();
	// Deferred: [ new_field_id => params-with-ref-tokens ] for a second remap pass.
	$deferred_fields = array();
	$deferred_groups = array(); // new_group_id => conditions (ref tokens)

	foreach( $data['groups'] as $group ) {

		$gref = isset( $group['ref'] ) ? $group['ref'] : 'g' . count( $new_group_ids );
		$meta = isset( $group['meta'] ) && is_array( $group['meta'] ) ? $group['meta'] : array();

		$new_group_id = wp_insert_post( array(
			'post_type'   => 'pewc_group',
			'post_status' => 'publish',
			'post_parent' => $product_id,
			'post_title'  => isset( $meta['group_title'] ) ? $meta['group_title'] : __( 'Imported group', 'pewc' ),
		), true );

		if( is_wp_error( $new_group_id ) ) {
			return $new_group_id;
		}

		$ref_to_group[ $gref ] = $new_group_id;
		$new_group_ids[]       = $new_group_id;

		foreach( pewc_portable_group_meta_keys() as $key ) {
			if( 'conditions' === $key ) {
				continue; // handled in the remap pass
			}
			if( array_key_exists( $key, $meta ) ) {
				update_post_meta( $new_group_id, $key, pewc_portable_sanitise_meta( $meta[ $key ] ) );
			}
		}
		if( ! empty( $meta['conditions'] ) && is_array( $meta['conditions'] ) ) {
			$deferred_groups[ $new_group_id ] = $meta['conditions'];
		}

		$group_field_ids = array();

		foreach( (array) $group['fields'] as $field ) {

			$fref   = isset( $field['ref'] ) ? $field['ref'] : $gref . 'f' . count( $group_field_ids );
			$params = isset( $field['params'] ) && is_array( $field['params'] ) ? $field['params'] : array();
			$media  = isset( $field['media'] ) && is_array( $field['media'] ) ? $field['media'] : array();

			$new_field_id = wp_insert_post( array(
				'post_type'   => 'pewc_field',
				'post_status' => 'publish',
				'post_parent' => $new_group_id,
				'post_title'  => isset( $params['field_label'] ) ? $params['field_label'] : __( 'Imported field', 'pewc' ),
			), true );

			if( is_wp_error( $new_field_id ) ) {
				return $new_field_id;
			}

			$ref_to_field[ $fref ] = $new_field_id;
			$group_field_ids[]     = $new_field_id;

			// Import images. Track failures per field for the report.
			$img_ok   = 0;
			$img_fail = 0;

			if( pewc_portable_media_has_source( isset( $media['field_image'] ) ? $media['field_image'] : null ) ) {
				$att = pewc_portable_sideload( $media['field_image'], $product_id, $image_cache );
				if( $att ) {
					$params['field_image'] = $att;
					$img_ok++;
				} else {
					$img_fail++;
				}
			}
			if( ! empty( $media['field_options'] ) && ! empty( $params['field_options'] ) ) {
				foreach( $media['field_options'] as $oi => $oks ) {
					if( ! is_array( $oks ) ) {
						continue;
					}
					foreach( $oks as $ok => $desc ) {
						if( ! pewc_portable_media_has_source( $desc ) ) {
							continue;
						}
						$att = pewc_portable_sideload( $desc, $product_id, $image_cache );
						if( $att && isset( $params['field_options'][ $oi ] ) ) {
							$params['field_options'][ $oi ][ $ok ] = $att;
							$img_ok++;
						} else {
							$img_fail++;
						}
					}
				}
			}
			if( ! empty( $media['field_rows'] ) && ! empty( $params['field_rows'] ) ) {
				foreach( $media['field_rows'] as $ri => $desc ) {
					$img = isset( $desc['image'] ) ? $desc['image'] : null;
					if( ! pewc_portable_media_has_source( $img ) ) {
						continue;
					}
					$att = pewc_portable_sideload( $img, $product_id, $image_cache );
					if( $att && isset( $params['field_rows'][ $ri ] ) ) {
						$params['field_rows'][ $ri ]['image'] = $att;
						$img_ok++;
					} else {
						$img_fail++;
					}
				}
			}

			if( $img_fail ) {
				$label = isset( $params['field_label'] ) && '' !== $params['field_label'] ? $params['field_label'] : $fref;
				$report[] = sprintf(
					/* translators: 1: number of images, 2: field label */
					_n( '%1$d image for "%2$s" could not be imported.', '%1$d images for "%2$s" could not be imported.', $img_fail, 'pewc' ),
					$img_fail,
					$label
				);
			}

			// Products field: map child_products keys -> real product IDs, and
			// rebuild field_default (CSV of default child IDs) from child_default_keys.
			$ftype = isset( $params['field_type'] ) ? $params['field_type'] : '';
			if( ( 'products' === $ftype || 'product-categories' === $ftype ) && ! empty( $params['child_products'] ) && is_array( $params['child_products'] ) ) {
				$mapped = array();
				foreach( $params['child_products'] as $ckey ) {
					// Already an int ID (file exported without child data)? keep only if it exists.
					if( is_numeric( $ckey ) ) {
						if( wc_get_product( (int) $ckey ) ) {
							$mapped[] = (int) $ckey;
						}
						continue;
					}
					if( isset( $child_key_map[ $ckey ] ) ) {
						$mapped[] = $child_key_map[ $ckey ];
					}
				}
				$params['child_products'] = $mapped;
			}
			if( ! empty( $field['child_default_keys'] ) && is_array( $field['child_default_keys'] ) ) {
				$def_ids = array();
				foreach( $field['child_default_keys'] as $dkey ) {
					if( isset( $child_key_map[ $dkey ] ) ) {
						$def_ids[] = $child_key_map[ $dkey ];
					}
				}
				$params['field_default'] = implode( ',', $def_ids );
			}

			$params['field_id'] = $new_field_id;
			$params['group_id'] = $new_group_id;
			$params['id']       = 'pewc_group_' . $new_group_id . '_' . $new_field_id;

			$deferred_fields[ $new_field_id ] = $params;

		}

		update_post_meta( $new_group_id, 'field_ids', $group_field_ids );

	}

	// ---- Second pass: remap ref: tokens now that every field has a real ID ----

	foreach( $deferred_fields as $new_field_id => $params ) {

		if( ! empty( $params['condition_field'] ) && is_array( $params['condition_field'] ) ) {
			foreach( $params['condition_field'] as $ci => $token ) {
				$params['condition_field'][ $ci ] = pewc_portable_ref_to_id_string( $token, $ref_to_group, $ref_to_field );
			}
		}
		if( ! empty( $params['formula'] ) && is_string( $params['formula'] ) ) {
			$params['formula'] = pewc_portable_refs_to_formula( $params['formula'], $ref_to_field );
		}

		$params = apply_filters( 'pewc_portable_import_field_params', $params, $new_field_id, $ref_to_field, $ref_to_group );

		// Write each param individually AND as the all_params blob (matches the save path).
		$multiples  = pewc_portable_array_params();
		$all_params = array( 'field_id' => $new_field_id );
		foreach( $field_params as $param ) {
			if( array_key_exists( $param, $params ) ) {
				$value = pewc_portable_sanitise_meta( $params[ $param ] );
				// Params the templates iterate over must be arrays - a scalar from a
				// malformed file would fatal the product edit screen.
				if( in_array( $param, $multiples, true ) && ! is_array( $value ) ) {
					$value = ( '' === $value || null === $value ) ? array() : array( $value );
				}
				update_post_meta( $new_field_id, $param, $value );
				$all_params[ $param ] = $value;
			}
		}
		// Preserve id/group_id which aren't in the param list.
		$all_params['id']       = $params['id'];
		$all_params['group_id'] = $params['group_id'];
		update_post_meta( $new_field_id, 'all_params', $all_params );
		delete_transient( 'pewc_item_object_' . $new_field_id );

	}

	foreach( $deferred_groups as $new_group_id => $conditions ) {
		$out = array();
		foreach( $conditions as $condition ) {
			if( empty( $condition['field'] ) ) {
				continue;
			}
			$id_string = pewc_portable_ref_to_id_string( $condition['field'], $ref_to_group, $ref_to_field );
			if( '' === $id_string ) {
				continue; // referenced field wasn't imported
			}
			$condition['field'] = $id_string;
			$out[] = pewc_portable_sanitise_meta( $condition );
		}
		if( $out ) {
			update_post_meta( $new_group_id, 'conditions', $out );
		}
	}

	// ---- Attach to the product ----

	$existing = pewc_get_group_order( $product_id );
	$order    = $existing ? array_filter( array_map( 'absint', explode( ',', $existing ) ) ) : array();
	$order    = array_merge( $order, $new_group_ids );
	update_post_meta( $product_id, 'group_order', implode( ',', $order ) );
	update_post_meta( $product_id, '_pewc_has_extra_fields', 'yes' );

	delete_transient( 'pewc_extra_fields_' . $product_id );
	delete_transient( 'pewc_has_extra_fields_' . $product_id );

	// ---- Build the human-readable report ----

	$notes = isset( $data['notes'] ) ? $data['notes'] : array();
	$report = array_merge( $report, pewc_portable_report_lines( $notes ) );

	return array(
		'imported_groups' => count( $new_group_ids ),
		'imported_fields' => count( $deferred_fields ),
		'report'          => $report,
		'edit_url'        => add_query_arg( 'pewc_reopen_addons', '1', get_edit_post_link( $product_id, 'raw' ) ),
	);

}

/**
 * Turn the export's "notes" into readable warning lines.
 * @since 4.4.6
 */
function pewc_portable_report_lines( $notes ) {
	$lines = array();
	$buckets = array(
		'dropped_product_refs'   => __( 'Product references were removed (set these up manually): %s', 'pewc' ),
		'dropped_variation_refs' => __( 'Variation-specific settings were removed (field will show for all variations): %s', 'pewc' ),
		'dropped_field_refs'     => __( 'Some field-to-field references could not be transferred: %s', 'pewc' ),
		'dropped_category_refs'  => __( 'Category references were removed: %s', 'pewc' ),
		'dropped_uploads'        => __( 'Default uploaded files were removed: %s', 'pewc' ),
	);
	foreach( $buckets as $key => $template ) {
		if( empty( $notes[ $key ] ) || ! is_array( $notes[ $key ] ) ) {
			continue;
		}
		$labels = array();
		foreach( $notes[ $key ] as $entry ) {
			$labels[] = ! empty( $entry['label'] ) ? $entry['label'] . ' (' . $entry['param'] . ')' : $entry['param'];
		}
		$lines[] = sprintf( $template, implode( ', ', array_unique( $labels ) ) );
	}
	if( ! empty( $notes['uses_global_groups'] ) ) {
		$lines[] = __( 'The source product also used global groups. Global groups are not included in the export - assign them separately if needed.', 'pewc' );
	}
	return $lines;
}

/**
 * Whether a media descriptor carries something we can import (embedded bytes or a URL).
 * @since 4.4.6
 */
function pewc_portable_media_has_source( $desc ) {
	return is_array( $desc ) && ( ! empty( $desc['data'] ) || ! empty( $desc['url'] ) );
}

/**
 * Import one media descriptor into the media library. Prefers embedded base64
 * bytes (`data`), then a bundled plugin:// path, then an http(s):// URL.
 * Caches by URL/filename for the duration of one import.
 * @since 4.4.6
 * @return int  Attachment ID, or 0 on failure.
 */
function pewc_portable_sideload( $desc, $product_id, &$cache ) {

	$url      = isset( $desc['url'] ) ? $desc['url'] : '';
	$data     = isset( $desc['data'] ) ? $desc['data'] : '';
	$filename = ! empty( $desc['filename'] ) ? sanitize_file_name( $desc['filename'] ) : '';
	$alt      = isset( $desc['alt'] ) ? $desc['alt'] : '';

	$cache_key = $url ? $url : ( 'data:' . md5( (string) $data ) );
	if( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}
	if( ! $url && ! $data ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attachment_id = 0;
	$tmp           = '';

	if( $data ) {

		// --- Embedded base64 bytes: no HTTP needed ---
		$bytes = base64_decode( $data, true );
		if( false === $bytes || '' === $bytes ) {
			$cache[ $cache_key ] = 0;
			return 0;
		}
		if( ! $filename ) {
			$filename = 'imported-image';
		}
		$tmp = wp_tempnam( $filename );
		if( ! $tmp || false === file_put_contents( $tmp, $bytes ) ) {
			if( $tmp ) {
				@unlink( $tmp );
			}
			$cache[ $cache_key ] = 0;
			return 0;
		}
		$file_array = array( 'name' => $filename, 'tmp_name' => $tmp );
		$attachment_id = @media_handle_sideload( $file_array, $product_id );

	} elseif( 0 === strpos( $url, 'plugin://' ) ) {

		// --- Bundled demo asset ---
		$rel = ltrim( substr( $url, strlen( 'plugin://' ) ), '/' );
		if( false !== strpos( $rel, '..' ) ) {
			$cache[ $cache_key ] = 0;
			return 0;
		}
		$path = trailingslashit( PEWC_PLUGIN_DIR_PATH ) . $rel;
		if( ! is_readable( $path ) ) {
			$cache[ $cache_key ] = 0;
			return 0;
		}
		$tmp = wp_tempnam( wp_basename( $path ) );
		if( ! $tmp || ! @copy( $path, $tmp ) ) {
			$cache[ $cache_key ] = 0;
			return 0;
		}
		$file_array = array(
			'name'     => $filename ? $filename : wp_basename( $path ),
			'tmp_name' => $tmp,
		);
		$attachment_id = @media_handle_sideload( $file_array, $product_id );

	} else {

		// --- Remote URL fallback ---
		$attachment_id = @media_sideload_image( $url, $product_id, $alt, 'id' );

	}

	// @-silence above: exif_read_data() in wp_read_image_metadata() warns on
	// images with a malformed EXIF block (WP core #42480). Not fatal.

	if( is_wp_error( $attachment_id ) ) {
		if( function_exists( 'pewc_error_log' ) ) {
			pewc_error_log( 'Portable import: could not import image ' . ( $url ? $url : $filename ) . ' - ' . $attachment_id->get_error_message() );
		}
		if( $tmp && file_exists( $tmp ) ) {
			@unlink( $tmp );
		}
		$attachment_id = 0;
	}

	if( $attachment_id && $alt ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
	}

	$cache[ $cache_key ] = (int) $attachment_id;
	return (int) $attachment_id;

}

/* -------------------------------------------------------------------------
 * Child product import
 * ---------------------------------------------------------------------- */

/**
 * Ensure a product_cat term exists for every referenced slug.
 * @since 4.4.6
 * @param array $categories  slug => name
 */
function pewc_portable_recreate_categories( $categories ) {
	foreach( $categories as $slug => $name ) {
		$slug = sanitize_title( $slug );
		if( ! $slug || term_exists( $slug, 'product_cat' ) ) {
			continue;
		}
		wp_insert_term(
			$name ? sanitize_text_field( $name ) : $slug,
			'product_cat',
			array( 'slug' => $slug )
		);
	}
}

/**
 * Resolve one exported child product to a product ID on this site:
 * match by SKU, then by exact published title, then create (if allowed).
 * @since 4.4.6
 * @param array $entry            One entry from $data['child_products'].
 * @param bool  $create_children
 * @param array $matched          (by ref) names of matched products, for the report.
 * @param array $created          (by ref) names of created products, for the report.
 * @return int  Product ID, or 0.
 */
function pewc_portable_resolve_child_product( $entry, $create_children, &$matched, &$created ) {

	$match   = isset( $entry['match'] ) && is_array( $entry['match'] ) ? $entry['match'] : array();
	$product = isset( $entry['product'] ) && is_array( $entry['product'] ) ? $entry['product'] : array();
	$name    = isset( $match['title'] ) && '' !== $match['title'] ? $match['title'] : ( isset( $match['sku'] ) ? $match['sku'] : '' );

	// 1. SKU match
	if( ! empty( $match['sku'] ) ) {
		$existing = wc_get_product_id_by_sku( $match['sku'] );
		if( $existing ) {
			$matched[] = $name;
			return (int) $existing;
		}
	}

	// 2. Exact published title match
	if( ! empty( $match['title'] ) ) {
		$q = new WP_Query( array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'title'                  => $match['title'],
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		) );
		if( ! empty( $q->posts ) ) {
			$matched[] = $name;
			return (int) $q->posts[0];
		}
	}

	// 3. Create
	if( ! $create_children || ! $product ) {
		return 0;
	}
	$new_id = pewc_portable_create_child_product( $product );
	if( $new_id ) {
		$created[] = $name;
	}
	return $new_id;

}

/**
 * Create a simple WooCommerce product from an exported child-product payload.
 * @since 4.4.6
 * @return int  New product ID, or 0 on failure.
 */
function pewc_portable_create_child_product( $p ) {

	if( ! function_exists( 'wc_get_product' ) || ! class_exists( 'WC_Product_Simple' ) ) {
		return 0;
	}

	$product = new WC_Product_Simple();

	$product->set_name( isset( $p['title'] ) ? sanitize_text_field( $p['title'] ) : __( 'Imported product', 'pewc' ) );
	$product->set_status( in_array( $p['status'] ?? 'publish', array( 'publish', 'draft', 'pending', 'private' ), true ) ? $p['status'] : 'publish' );
	if( ! empty( $p['catalog_visibility'] ) ) {
		$product->set_catalog_visibility( $p['catalog_visibility'] );
	}

	if( ! empty( $p['sku'] ) && ! wc_get_product_id_by_sku( $p['sku'] ) ) {
		$product->set_sku( wc_clean( $p['sku'] ) );
	}

	if( isset( $p['regular_price'] ) && '' !== $p['regular_price'] ) {
		$product->set_regular_price( wc_format_decimal( $p['regular_price'] ) );
	}
	if( isset( $p['sale_price'] ) && '' !== $p['sale_price'] ) {
		$product->set_sale_price( wc_format_decimal( $p['sale_price'] ) );
	}
	if( ! empty( $p['date_on_sale_from'] ) ) {
		$product->set_date_on_sale_from( $p['date_on_sale_from'] );
	}
	if( ! empty( $p['date_on_sale_to'] ) ) {
		$product->set_date_on_sale_to( $p['date_on_sale_to'] );
	}

	if( isset( $p['short_description'] ) ) {
		$product->set_short_description( wp_kses_post( $p['short_description'] ) );
	}
	if( isset( $p['description'] ) ) {
		$product->set_description( wp_kses_post( $p['description'] ) );
	}

	if( ! empty( $p['tax_status'] ) ) {
		$product->set_tax_status( $p['tax_status'] );
	}
	if( isset( $p['tax_class'] ) ) {
		$product->set_tax_class( $p['tax_class'] );
	}

	$product->set_manage_stock( ! empty( $p['manage_stock'] ) );
	if( ! empty( $p['manage_stock'] ) && isset( $p['stock_quantity'] ) && null !== $p['stock_quantity'] ) {
		$product->set_stock_quantity( $p['stock_quantity'] );
	}
	if( ! empty( $p['stock_status'] ) ) {
		$product->set_stock_status( $p['stock_status'] );
	}
	if( ! empty( $p['backorders'] ) ) {
		$product->set_backorders( $p['backorders'] );
	}
	$product->set_sold_individually( ! empty( $p['sold_individually'] ) );
	$product->set_virtual( ! empty( $p['virtual'] ) );
	$product->set_downloadable( ! empty( $p['downloadable'] ) );

	foreach( array( 'weight', 'length', 'width', 'height' ) as $dim ) {
		if( isset( $p[ $dim ] ) && '' !== $p[ $dim ] ) {
			$product->{'set_' . $dim}( wc_format_decimal( $p[ $dim ] ) );
		}
	}
	if( isset( $p['menu_order'] ) ) {
		$product->set_menu_order( (int) $p['menu_order'] );
	}

	// Categories (slugs -> term IDs, created if missing).
	if( ! empty( $p['category_slugs'] ) && is_array( $p['category_slugs'] ) ) {
		$term_ids = array();
		foreach( $p['category_slugs'] as $slug ) {
			$slug = sanitize_title( $slug );
			if( ! $slug ) {
				continue;
			}
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			if( ! $term ) {
				$new = wp_insert_term( $slug, 'product_cat', array( 'slug' => $slug ) );
				if( ! is_wp_error( $new ) ) {
					$term_ids[] = (int) $new['term_id'];
				}
			} else {
				$term_ids[] = (int) $term->term_id;
			}
		}
		if( $term_ids ) {
			$product->set_category_ids( $term_ids );
		}
	}

	$new_id = $product->save();
	if( ! $new_id || is_wp_error( $new_id ) ) {
		return 0;
	}

	// Images (reuse the add-on image sideloader; honours the embed setting).
	$cache = array();
	if( ! empty( $p['image'] ) && pewc_portable_media_has_source( $p['image'] ) ) {
		$att = pewc_portable_sideload( $p['image'], $new_id, $cache );
		if( $att ) {
			$img_product = wc_get_product( $new_id );
			$img_product->set_image_id( $att );
			$img_product->save();
		}
	}
	if( ! empty( $p['gallery'] ) && is_array( $p['gallery'] ) ) {
		$gids = array();
		foreach( $p['gallery'] as $g ) {
			if( pewc_portable_media_has_source( $g ) ) {
				$att = pewc_portable_sideload( $g, $new_id, $cache );
				if( $att ) {
					$gids[] = $att;
				}
			}
		}
		if( $gids ) {
			$img_product = wc_get_product( $new_id );
			$img_product->set_gallery_image_ids( $gids );
			$img_product->save();
		}
	}

	return (int) $new_id;

}

/**
 * "ref:g0f1" -> "pewc_group_<newG>_<newF>". Returns '' if the ref is unknown.
 * @since 4.4.6
 */
function pewc_portable_ref_to_id_string( $token, $ref_to_group, $ref_to_field ) {
	if( ! is_string( $token ) || 0 !== strpos( $token, 'ref:' ) ) {
		return is_string( $token ) ? $token : '';
	}
	$ref = substr( $token, 4 );
	if( ! isset( $ref_to_field[ $ref ] ) ) {
		return '';
	}
	$field_id = $ref_to_field[ $ref ];
	$group_id = wp_get_post_parent_id( $field_id );
	return 'pewc_group_' . $group_id . '_' . $field_id;
}

/**
 * Rewrite {field_ref:g0f1} tokens back to {field_<newId>}.
 * @since 4.4.6
 */
function pewc_portable_refs_to_formula( $formula, $ref_to_field ) {
	foreach( $ref_to_field as $ref => $new_id ) {
		$formula = str_replace( '{field_ref:' . $ref, '{field_' . $new_id, $formula );
	}
	// Any leftover unresolved ref tokens -> 0 to avoid broken formulas.
	$formula = preg_replace( '/\{field_ref:[a-z0-9]+/i', '{field_0', $formula );
	return $formula;
}

/**
 * Light recursive sanitisation for imported meta values.
 * Field markup (e.g. group_description) is run through wp_kses_post.
 * @since 4.4.6
 */
function pewc_portable_sanitise_meta( $value ) {
	if( is_array( $value ) ) {
		$out = array();
		foreach( $value as $k => $v ) {
			$out[ is_string( $k ) ? sanitize_text_field( $k ) : $k ] = pewc_portable_sanitise_meta( $v );
		}
		return $out;
	}
	if( is_string( $value ) ) {
		// Allow basic HTML - descriptions and information fields contain markup.
		return wp_kses_post( $value );
	}
	return $value;
}

/* -------------------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------------------- */

/**
 * Enqueue the portable import script wherever the base admin script runs.
 * @since 4.4.6
 */
function pewc_portable_enqueue_scripts( $hook ) {

	if( wp_script_is( 'pewc-portable-script', 'enqueued' ) ) {
		return;
	}

	$on_tool_page = is_string( $hook ) && ( false !== strpos( $hook, 'pewc-export-addons' ) || false !== strpos( $hook, 'pewc-import-addons' ) );

	// The product Add-Ons panel needs the base script; the standalone tool pages
	// need jQuery + wc-enhanced-select for the product search box.
	if( ! wp_script_is( 'pewc-admin-script', 'enqueued' ) && ! $on_tool_page ) {
		return;
	}

	$version = defined( 'PEWC_SCRIPT_DEBUG' ) && PEWC_SCRIPT_DEBUG ? time() : PEWC_PLUGIN_VERSION;
	$deps    = wp_script_is( 'pewc-admin-script', 'registered' ) ? array( 'pewc-admin-script' ) : array( 'jquery', 'wc-enhanced-select' );
	$deps[]  = 'jquery-blockui';

	if( $on_tool_page ) {
		wp_enqueue_style( 'woocommerce_admin_styles' );
		wp_enqueue_script( 'wc-enhanced-select' );
	}

	wp_enqueue_style( 'pewc-portable-style', trailingslashit( PEWC_PLUGIN_URL ) . 'assets/css/admin-portable-import.css', array(), $version );
	wp_register_script( 'pewc-portable-script', trailingslashit( PEWC_PLUGIN_URL ) . 'assets/js/admin-portable-import.js', $deps, $version, true );
	wp_enqueue_script( 'pewc-portable-script' );

	wp_localize_script( 'pewc-portable-script', 'pewc_portable_vars', array(
		'ajaxurl'            => admin_url( 'admin-ajax.php' ),
		'import_nonce'       => wp_create_nonce( 'pewc_portable_import' ),
		'export_check_nonce' => wp_create_nonce( 'pewc_portable_export_check' ),
		'export_page'        => admin_url( 'admin.php?page=pewc-export-addons' ),
		'i18n'               => array(
			'choose_product'         => __( 'Please choose a product first.', 'pewc' ),
			'choose_file'            => __( 'Please choose an export file.', 'pewc' ),
			'choose_demo'            => __( 'Please choose a demo product.', 'pewc' ),
			'working'                => __( 'Working&hellip;', 'pewc' ),
			'done'                   => __( 'Done.', 'pewc' ),
			'imported'               => __( 'Imported %1$d group(s) and %2$d field(s).', 'pewc' ),
			'reloading'              => __( 'Reloading to show the new add-ons&hellip;', 'pewc' ),
			'go_to_product'          => __( 'Go to product', 'pewc' ),
			'error'                  => __( 'Something went wrong.', 'pewc' ),
			'confirm_demo'           => __( 'Add the add-on groups from this demo product to the current product?', 'pewc' ),
			'confirm_export_children' => __( 'This product has a Products or Product Categories field. Do you wish to export the simple products it references so they can be recreated on the destination site? Variable products and product variations are never included.', 'pewc' ),
			'confirm_import_children' => __( 'This file includes %d referenced product(s). Any that are not found on this site by SKU or name will be created as new products. Continue?', 'pewc' ),
			'export_created'         => __( 'Export file created.', 'pewc' ),
			'export_children_note'   => __( '%d referenced product(s) included in the file.', 'pewc' ),
		),
	) );

}
add_action( 'admin_enqueue_scripts', 'pewc_portable_enqueue_scripts', 110 );
