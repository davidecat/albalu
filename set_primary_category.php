<?php
/**
 * Imposta la categoria primaria Yoast (_yoast_wpseo_primary_product_cat)
 * sui prodotti elencati nel CSV fornito dal consulente SEO.
 *
 * Uso (dalla root di WordPress):
 *   wp eval-file set_primary_category.php albalu_categorie_primarie.csv           # simulazione
 *   wp eval-file set_primary_category.php albalu_categorie_primarie.csv applica   # scrive
 *
 * Senza la parola "applica" non scrive nulla: elenca solo cosa cambierebbe.
 * Salta le righe di casistica D, i prodotti non trovati, le categorie
 * inesistenti e i casi in cui la categoria indicata non è già assegnata al
 * prodotto: quelli li segnala nel log invece di assegnarla di sua iniziativa.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Eseguire con: wp eval-file set_primary_category.php <csv> [applica]\n" );
}

$csv_path = $args[0] ?? '';
$apply    = ( ( $args[1] ?? '' ) === 'applica' );

if ( ! $csv_path || ! file_exists( $csv_path ) ) {
	WP_CLI::error( "CSV non trovato: '{$csv_path}'" );
}

$META_KEY = '_yoast_wpseo_primary_product_cat';
$TAX      = 'product_cat';

$fh = fopen( $csv_path, 'r' );
if ( ! $fh ) {
	WP_CLI::error( "Impossibile aprire il CSV: {$csv_path}" );
}

// Intestazione (gestisce il BOM)
$header = fgetcsv( $fh, 0, ';' );
if ( ! $header ) {
	WP_CLI::error( 'CSV vuoto o illeggibile.' );
}
$header[0] = preg_replace( '/^\xEF\xBB\xBF/', '', $header[0] );
$header    = array_map( 'trim', $header );

foreach ( array( 'casistica', 'url_prodotto', 'primaria_da_impostare', 'url_primaria_da_impostare' ) as $needed ) {
	if ( ! in_array( $needed, $header, true ) ) {
		WP_CLI::error( "Colonna mancante nel CSV: {$needed}" );
	}
}

/** Risolve un term di product_cat dall'URL, con fallback sul nome. */
function albalu_resolve_term( $url, $name, $tax ) {
	$url = trim( (string) $url );
	if ( $url ) {
		$slug = trim( wp_parse_url( $url, PHP_URL_PATH ) ?? '', '/' );
		if ( $slug ) {
			$parts = explode( '/', $slug );
			$last  = end( $parts );
			$term  = get_term_by( 'slug', $last, $tax );
			if ( $term && ! is_wp_error( $term ) ) {
				return $term;
			}
		}
	}
	$name = trim( (string) $name );
	if ( $name ) {
		$term = get_term_by( 'name', $name, $tax );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term;
		}
	}
	return null;
}

$stat = array(
	'totali' => 0, 'saltate_D' => 0, 'aggiornate' => 0, 'gia_corrette' => 0,
	'prodotto_non_trovato' => 0, 'categoria_non_trovata' => 0, 'categoria_non_assegnata' => 0,
);
$problemi = array();

WP_CLI::line( $apply
	? "== MODALITÀ SCRITTURA: le modifiche verranno salvate =="
	: "== SIMULAZIONE: nessuna modifica verrà salvata (aggiungere 'applica' per scrivere) ==" );
WP_CLI::line( '' );

while ( ( $row = fgetcsv( $fh, 0, ';' ) ) !== false ) {
	if ( count( $row ) === 1 && trim( (string) $row[0] ) === '' ) {
		continue;
	}
	$r = array_combine( $header, array_pad( array_slice( $row, 0, count( $header ) ), count( $header ), '' ) );
	$stat['totali']++;

	if ( strtoupper( trim( $r['casistica'] ) ) === 'D' ) {
		$stat['saltate_D']++;
		continue;
	}

	$titolo  = trim( $r['title'] ?? '' );
	$post_id = url_to_postid( trim( $r['url_prodotto'] ) );
	if ( ! $post_id || get_post_type( $post_id ) !== 'product' ) {
		$stat['prodotto_non_trovato']++;
		$problemi[] = "PRODOTTO NON TROVATO | {$r['url_prodotto']}";
		continue;
	}

	$term = albalu_resolve_term( $r['url_primaria_da_impostare'], $r['primaria_da_impostare'], $TAX );
	if ( ! $term ) {
		$stat['categoria_non_trovata']++;
		$problemi[] = "CATEGORIA INESISTENTE | '{$r['primaria_da_impostare']}' | {$titolo}";
		continue;
	}

	$assegnate = wp_get_post_terms( $post_id, $TAX, array( 'fields' => 'ids' ) );
	if ( is_wp_error( $assegnate ) || ! in_array( (int) $term->term_id, array_map( 'intval', $assegnate ), true ) ) {
		$stat['categoria_non_assegnata']++;
		$problemi[] = "CATEGORIA NON ASSEGNATA AL PRODOTTO | '{$term->name}' | {$titolo}";
		continue;
	}

	$attuale = (int) get_post_meta( $post_id, $META_KEY, true );
	if ( $attuale === (int) $term->term_id ) {
		$stat['gia_corrette']++;
		continue;
	}

	$da = $attuale ? ( get_term( $attuale, $TAX )->name ?? $attuale ) : '(nessuna)';
	WP_CLI::line( sprintf(
		'%s [%s] %s | %s -> %s',
		$apply ? 'SCRITTO ' : 'DA FARE ',
		$r['casistica'],
		mb_substr( $titolo, 0, 52 ),
		mb_substr( $da, 0, 34 ),
		$term->name
	) );

	if ( $apply ) {
		update_post_meta( $post_id, $META_KEY, (int) $term->term_id );
	}
	$stat['aggiornate']++;
}
fclose( $fh );

if ( $problemi ) {
	WP_CLI::line( '' );
	WP_CLI::line( '== RIGHE SALTATE CHE RICHIEDONO ATTENZIONE ==' );
	foreach ( $problemi as $p ) {
		WP_CLI::line( '  ' . $p );
	}
}

WP_CLI::line( '' );
WP_CLI::line( '== RIEPILOGO ==' );
foreach ( $stat as $k => $v ) {
	WP_CLI::line( sprintf( '  %-26s %d', str_replace( '_', ' ', $k ), $v ) );
}

if ( $apply ) {
	WP_CLI::line( '' );
	WP_CLI::success( 'Fatto. Ora eseguire: wp yoast index --reindex  (poi svuotare la cache FastCGI).' );
} else {
	WP_CLI::line( '' );
	WP_CLI::success( 'Simulazione completata: nessuna modifica salvata.' );
}
