<?php
global $wpdb;
$rows = $wpdb->get_results("SELECT pm.post_id, p.post_type, p.post_status, p.post_name, LEFT(p.post_title,60) AS titolo
	FROM {$wpdb->prefix}postmeta pm
	LEFT JOIN {$wpdb->prefix}posts p ON p.ID = pm.post_id
	WHERE pm.meta_key = 'pewc_product_extra_fields'
	  AND (pm.meta_value LIKE '%43227%' OR pm.meta_value LIKE '%43211%')");
echo "=== dove sono assegnati i gruppi CONF10 ===\n";
foreach ( $rows as $r ) {
	echo "  post {$r->post_id} | {$r->post_type} | {$r->post_status} | {$r->titolo}\n";
	echo "     " . get_permalink( $r->post_id ) . "\n";
}
if ( ! $rows ) echo "  nessuno\n";

echo "\n=== gruppi assegnati per categoria / globali ===\n";
foreach ( array( 43211, 43227 ) as $gid ) {
	echo "  gruppo $gid:\n";
	foreach ( array( 'group_products', 'group_categories', 'apply_globally', 'group_include', 'group_exclude' ) as $k ) {
		$v = get_post_meta( $gid, $k, true );
		if ( $v !== '' && $v !== null && $v !== array() ) {
			echo "     $k = " . substr( is_array( $v ) ? json_encode( $v ) : $v, 0, 160 ) . "\n";
		}
	}
}
