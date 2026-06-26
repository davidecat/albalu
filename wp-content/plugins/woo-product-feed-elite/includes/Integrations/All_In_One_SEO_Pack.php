<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Integrations
 */

namespace AdTribes\PFE\Integrations;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Traits\Singleton_Trait;

/**
 * WP_Rocket class.
 *
 * @since 4.9.7
 */
class All_In_One_SEO_Pack extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Check if All_in_One_SEO_Pack plugin is active.
     *
     * @since 4.9.7
     * @return bool
     */
    public function is_active() {
        return class_exists( 'All_in_One_SEO_Pack' );
    }

    /**
     * Add All_in_One_SEO_Pack attributes to the product feed.
     *
     * @since 4.9.7
     * @acceess public
     *
     * @param array $attributes Array of attributes to add to the product feed.
     * @return array
     */
    public function add_all_in_one_seo_pack_attributes( $attributes ) {
        $aio_seo_attributes = array(
            'custom_attributes__aioseop_title'       => 'All in one seo pack title',
            'custom_attributes__aioseop_description' => 'All in one seo pack description',
        );

        $attributes['Added Custom Attributes'] = ! empty( $attributes['Added Custom Attributes'] ) ?
            array_merge( $attributes['Added Custom Attributes'], $aio_seo_attributes )
            : $aio_seo_attributes;

        return $attributes;
    }

    /**
     * Run All_in_One_SEO_Pack integration hooks.
     *
     * @since 4.9.7
     */
    public function run() {
        if ( ! $this->is_active() ) {
            return;
        }

        add_filter( 'adt_product_feed_attributes', array( $this, 'add_all_in_one_seo_pack_attributes' ), 10, 1 );
    }
}
