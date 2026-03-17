<?php
/**
 * Blog index - delegates to archive.php
 * Used when Posts page is set (e.g. /notizie/)
 */
defined( 'ABSPATH' ) || exit;

load_template( get_stylesheet_directory() . '/archive.php' );
