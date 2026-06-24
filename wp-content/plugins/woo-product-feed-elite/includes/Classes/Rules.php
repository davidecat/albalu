<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFE\Classes
 */

namespace AdTribes\PFE\Classes;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Traits\Singleton_Trait;
use AdTribes\PFP\Helpers\Field_Mapping_Helper;

/**
 * Rules class.
 *
 * @since 4.9.8
 */
class Rules extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Group label Pro injects with an `(Elite)` suffix as an upsell marker.
     *
     * Elite renames it to the translated, suffix-free form so options become
     * fully interactive on licensed sites. Mirrors the label-strip pattern
     * `add_elite_actions()` uses for `Set Attribute (Elite)` / `Exclude Attribute (Elite)`.
     *
     * @since 5.0.8
     */
    private const PRO_GROUP_LABEL = 'Feed output fields (Elite)';

    /**
     * Strip the `(Elite)` suffix from the field-mapping attributes group injected by Pro.
     *
     * Pro registers the group at priority 10; we run at 20 so the rename happens after
     * injection. Preserves option order by writing into a fresh array with the renamed
     * key in the same position.
     *
     * @since 5.0.8
     * @access public
     *
     * @param array $attributes Grouped attribute list keyed by group label.
     * @return array
     */
    public function strip_elite_group_label_from_attributes( $attributes ) {
        if ( ! is_array( $attributes ) || ! isset( $attributes[ self::PRO_GROUP_LABEL ] ) ) {
            return $attributes;
        }

        $renamed_label = __( 'Feed output fields', 'woo-product-feed-elite' );
        $rebuilt       = array();
        foreach ( $attributes as $group_label => $group_options ) {
            if ( self::PRO_GROUP_LABEL === $group_label ) {
                $rebuilt[ $renamed_label ] = $group_options;
                continue;
            }
            $rebuilt[ $group_label ] = $group_options;
        }

        return $rebuilt;
    }

    /**
     * Add the elite actions.
     *
     * @since 4.9.8
     * @access public
     *
     * @param array $actions The actions.
     * @return array
     */
    public function add_elite_actions( $actions ) {

        // Change the label of the set_attribute action, find it by value.
        $actions = array_map(
            function ( $action ) {
                if ( 'set_attribute' === $action['value'] ) {
                    $action['label'] = __( 'Set Attribute', 'woo-product-feed-elite' );
                } elseif ( 'exclude' === $action['value'] ) {
                    $action['label'] = __( 'Exclude Attribute', 'woo-product-feed-elite' );
                }
                return $action;
            },
            $actions
        );

        // Return the actions.
        return $actions;
    }

    /**
     * Process the elite actions.
     *
     * @since 4.9.8
     * @since 5.0.8 Route output-field actions to their underlying mapfrom so values land in the feed.
     * @access public
     *
     * @param array  $data The data.
     * @param array  $action The action.
     * @param object $feed The feed object.
     * @return array
     */
    public function process_elite_actions( $data, $action, $feed ) {
        switch ( $action['action'] ) {
            case 'set_attribute':
                $data[ $action['attribute'] ] = $data[ $action['value'] ] ?? ''; // Set the attribute to the value.
                break;
            case 'exclude':
                $this->exclude_attribute( $data, $action, $feed );
                break;
            default:
                // For Pro actions (set_value, findreplace, multiply, divide, plus, minus) where the
                // target attribute is a field-mapping output (e.g. `g:custom_label_0`), Pro's
                // process_action just set $data[output_name]. Copy that value over to the mapping's
                // mapfrom key so the field-mapping output picks it up. Skip static_value mappings —
                // their mapfrom holds a literal string, not a $data key.
                //
                // Catch-all: any future Pro action that writes to $data[ $action['attribute'] ] is
                // routed the same way. If a future Pro action writes elsewhere instead, the
                // array_key_exists guard in maybe_route_to_mapfrom will skip it as a no-op.
                $this->maybe_route_to_mapfrom( $data, $action, $feed );
                break;
        }
        return $data;
    }

    /**
     * Copy a rule-set value from an output-field key over to its underlying mapfrom key
     * so the field-mapping output stage picks it up.
     *
     * Static-value mappings are skipped — their `mapfrom` holds a literal output string
     * rather than a $data key, so writing to $data[mapfrom] there would corrupt all rows
     * that share the same static value.
     *
     * @since 5.0.8
     * @access private
     *
     * @param array  $data   The product data array (passed by reference).
     * @param array  $action The action data.
     * @param object $feed   The feed object.
     */
    private function maybe_route_to_mapfrom( &$data, $action, $feed ) {
        $attribute = $action['attribute'] ?? '';
        if ( '' === $attribute ) {
            return;
        }

        $field_mapping = Field_Mapping_Helper::get_feed_field_mapping( $feed );
        if ( null === $field_mapping ) {
            return;
        }

        // Only route if Pro's process_action actually wrote to $data[$attribute].
        if ( ! array_key_exists( $attribute, $data ) ) {
            return;
        }

        foreach ( $field_mapping as $field ) {
            if ( ( $field['attribute'] ?? '' ) !== $attribute ) {
                continue;
            }

            // Static-value mappings: mapfrom is the literal output string, not a data key.
            if ( array_key_exists( 'static_value', $field ) ) {
                return;
            }

            $mapfrom = $field['mapfrom'] ?? '';
            if ( '' === $mapfrom ) {
                return;
            }

            $data[ $mapfrom ] = $data[ $attribute ];
            return;
        }
    }

    /**
     * Exclude the attribute.
     *
     * @since 5.0.6
     * @access private
     *
     * @param array  $data The data.
     * @param array  $action The action.
     * @param object $feed The feed object.
     */
    private function exclude_attribute( &$data, $action, $feed ) {
        $field_mapping = Field_Mapping_Helper::get_feed_field_mapping( $feed );
        if ( null === $field_mapping ) {
            return;
        }

        foreach ( $field_mapping as $field ) {
            if ( ! empty( $field['attribute'] ) && ! empty( $field['mapfrom'] ) && $field['attribute'] === $action['attribute'] ) {
                unset( $data[ $field['mapfrom'] ] );
                break;
            }
        }
    }

    /**
     * Run the class
     *
     * @codeCoverageIgnore
     * @since 4.9.8
     */
    public function run() {
        add_filter( 'adt_pfp_get_rules_actions', array( $this, 'add_elite_actions' ) );
        add_filter( 'adt_pfp_process_rules_action', array( $this, 'process_elite_actions' ), 10, 3 );
        // Priority 20: runs after Pro's injection (priority 10) so the suffix-free label wins
        // regardless of plugin load order.
        add_filter( 'adt_pfp_get_filters_rules_attributes', array( $this, 'strip_elite_group_label_from_attributes' ), 20 );
    }
}
