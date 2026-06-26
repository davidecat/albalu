<?php
/**
 * Template for calendar list field
 * @since 4.1.0
 * @package WooCommerce Product Add-Ons Ultimate
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo $open_td;

if( isset( $item['field_rows'] ) ) {
	
  	$offset_index = 0; // Relative to the current day

	$number_columns = ( isset( $item['number_columns'] ) ) ? $item['number_columns'] : 3;
	$radio_wrapper_classes = array(
		'pewc-radio-images-wrapper',
		'pewc-calendar-list-wrapper'
	);
	$radio_wrapper_classes[] = 'pewc-columns-' . intval( $number_columns );
	$weekdays = is_array( $item['cl_weekdays'] ) ? array_keys( $item['cl_weekdays'] ) : array();
	$blocked_dates = ! empty( $item['cl_blocked_dates'] ) ? array_filter( array_map( 'trim', explode( ',', $item['cl_blocked_dates'] ) ) ) : array();

	// If the current time is past the latest time, skip today and start from tomorrow.
	// Use !== '' rather than !empty() so that minute=0 (e.g. 10:00) is treated as a valid value.
	$base_offset = 0;
	$has_latest_time = isset( $item['field_latest_hour'], $item['field_latest_minute'] )
		&& $item['field_latest_hour'] !== ''
		&& $item['field_latest_minute'] !== '';
	if( $has_latest_time ) {
		$latest_hour = intval( $item['field_latest_hour'] );
		$latest_minute = intval( $item['field_latest_minute'] );
		$cutoff_minutes = $latest_hour * 60 + $latest_minute;
		// If JS passed the client's local minutes-since-midnight (added to the URL on
		// cutoff-triggered reload), use that for comparison so the cutoff is always
		// evaluated in the user's local timezone regardless of server timezone config.
		if( isset( $_GET['pewc_client_minutes'] ) ) {
			$now_minutes = intval( $_GET['pewc_client_minutes'] );
		} else {
			$now = new DateTime( 'now', wp_timezone() );
			$now_minutes = intval( $now->format('H') ) * 60 + intval( $now->format('i') );
		}
		if( $now_minutes >= $cutoff_minutes ) {
			$base_offset = 1;
		}
	} ?>

	<div class="<?php echo join( ' ', $radio_wrapper_classes ); ?>">

  	<?php if( ! empty( $item['field_cl_options'] ) ) {

		// Pre-resolve all option dates so we can look ahead: if an option's resolved date
		// matches the next option's date (e.g. Option 0 shifts forward past the cutoff to
		// land on the same day as Option 1), skip the earlier option rather than the later one.
		$resolved_options = array();
		$is_first_pre = true;
		foreach( $item['field_cl_options'] as $option_index => $list_item ) {
			if( ! isset( $list_item['value'] ) || $list_item['value'] === false ) {
				continue;
			}
			$offset = absint( $list_item['value'] );
			$count  = $is_first_pre ? $offset + $base_offset : $offset;
			$is_first_pre = false;

			$d = new DateTime();
			if( $count === 0 ) {
				while( ! pewc_is_calendar_list_date_allowed( $d, $weekdays, $blocked_dates ) ) {
					$d->modify( '+1 day' );
				}
			} else {
				$counted = 0;
				while( $counted < $count ) {
					$d->modify( '+1 day' );
					if( pewc_is_calendar_list_date_allowed( $d, $weekdays, $blocked_dates ) ) {
						$counted++;
					}
				}
			}
			$resolved_options[] = array(
				'option_index' => $option_index,
				'list_item'    => $list_item,
				'date'         => $d,
				'ymd'          => $d->format( 'Y-m-d' ),
			);
		}

		// Build render list: drop any entry whose resolved date equals the following entry's date.
		$render_options  = array();
		$resolved_count  = count( $resolved_options );
		for( $ri = 0; $ri < $resolved_count; $ri++ ) {
			$next_ymd = isset( $resolved_options[ $ri + 1 ] ) ? $resolved_options[ $ri + 1 ]['ymd'] : null;
			if( $next_ymd !== null && $resolved_options[ $ri ]['ymd'] === $next_ymd ) {
				continue;
			}
			$render_options[] = $resolved_options[ $ri ];
		}

		$is_first_option = true;
		$last_rendered_date = null;

		foreach( $render_options as $resolved ) {

			$option_index = $resolved['option_index'];
			$list_item    = $resolved['list_item'];
			$option_date  = $resolved['date'];

			if( ! isset( $list_item['value'] ) || $list_item['value'] === false ) {
				continue;
			} else {
				$offset = absint( $list_item['value'] );
			}

			$is_first_rendered_option = $is_first_option;
			$is_first_option = false;

			// Skip this option if it resolves to the same or earlier date as the previously rendered option
			if( $last_rendered_date !== null && $option_date->format( 'Y-m-d' ) <= $last_rendered_date->format( 'Y-m-d' ) ) {
				continue;
			}
			$last_rendered_date = clone $option_date;

			$nice_day = wp_date( 'D', $option_date->getTimestamp() );
			$nice_date = $option_date->format( 'j' );

			$wrapper_classes = array(
				'pewc-cl-wrapper',
				'pewc-radio-image-wrapper',
				'pewc-radio-checkbox-image-wrapper',
				'pewc-radio-image-wrapper-' . $option_index
			);

			$time = '';
			$hour = '';
			$minute = '';
			if( $is_first_rendered_option && $base_offset === 0 ) {
				$hour = $has_latest_time ? $item['field_latest_hour'] : '';
				$minute = $has_latest_time ? $item['field_latest_minute'] : '';
				$time_label = ! empty( $item['field_time_label'] ) ?  $item['field_time_label'] : '';
				if( $has_latest_time ) {
					$hour = str_pad( $hour, 2, '0', STR_PAD_LEFT );
					$minute = str_pad( $minute, 2, '0', STR_PAD_LEFT );
					$time = sprintf(
						'%s&nbsp;%s:%s',
						$time_label,
						$hour,
						$minute
					);
				}
				$wrapper_classes[] = 'pewc-cl-has-time';
			}

			$price = ! empty( $list_item['price'] ) ? $list_item['price'] : 0;
			$option_price = pewc_get_option_price( $list_item, $item, $product );

			$classes = array( 'pewc-radio-form-field' );
			$classes = apply_filters( 'pewc_calendar_list_option_classes', $classes, $item, $offset, $price, $option_index );

			$radio_id = $id . '_' . strtolower( str_replace( ' ', '_', $list_item['value'] ) );

			$selected = selected( $list_item['value'], $value, false );
			if(  $list_item['value'] == $value ) {
				$wrapper_classes[] = 'checked';
			}

			printf(
				'<div class="%s" data-hour="%s" data-minute="%s" data-today="%s">',
				join( ' ', apply_filters( 'pewc_calendar_list_wrapper_classes', $wrapper_classes, $offset, $price, $option_index ) ),
				$hour,
				$minute,
				$option_date->format('Y-m-d')
			);

			printf(
				'<label for="%s" class="%s">',
				esc_attr( $nice_date ),
				join( ' ', apply_filters( 'pewc_calendar_list_label_classes', array(), $item, $offset, $option_index ) ),
			);

			printf(
				'<input data-option-cost="%s" %s type="radio" name="%s[]" id="%s" class="%s" data-date="%s" data-offset="%s" value="%s">',
				esc_attr( $option_price ),
				$selected,
				esc_attr( $id ),
				esc_attr( $radio_id ),
				join( ' ', $classes ),
				$option_date->format( 'Y-m-d' ),
				esc_attr( $list_item['value'] ),
				esc_attr( $option_index )
			);
			
			$price_visibility = pewc_get_price_visibility( $item ); ?>

			<span class="pewc-cl-date-box">
				<span class="pewc-cl-item pewc-cl-nice-day"><?php echo esc_html( $nice_day ); ?></span>
				<span class="pewc-cl-item pewc-cl-nice-date"><?php echo esc_html( $nice_date ); ?></span>
			</span>
			<span class="pewc-cl-info-box">
				<?php if( $price_visibility == 'visible' ) { ?>
				<span class="pewc-cl-price-box">
					<span class="pewc-cl-item pewc-cl-option-price"><?php echo wc_price( $option_price ); ?></span>
				</span>
				<?php } ?>
				<span class="pewc-cl-time-box">
					<span class="pewc-cl-item pewc-cl-time"><?php echo esc_html( $time ); ?></span>
				</span>
			</span>

			<?php echo '</label>'; // End label
			echo '</div>'; // End wrapper classes

	  }

	} ?>

</div><!-- .pewc-radio-images-wrapper -->

<?php
	printf(
		'<input type="hidden" id="%s" name="%s" value="%s">',
		'pewc_cl_' . $field_id,
		'pewc_cl_' . $field_id,
		''
	);
	printf(
		'<input type="hidden" id="%s" name="%s" value="%s">',
		'pewc_cl_price_' . $field_id,
		'pewc_cl_price_' . $field_id,
		''
	);
	printf(
		'<input type="hidden" id="%s" name="%s" value="%s">',
		'pewc_cl_offset_' . $id,
		'pewc_cl_offset_' . $id,
		''
	);
}

echo $close_td;