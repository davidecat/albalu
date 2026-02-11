<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class SS88_MediaLibraryFileSize_Analyze {

	protected $version = '1.6.8';

	public static function init() {

		$C = __CLASS__;
		new $C;

	}

	function __construct() {

		if(!is_admin()) return;

		add_action('admin_menu', [$this, 'admin_menu']);
		add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
		add_action('wp_ajax_SS88MLFS_analyzeSummary', [$this, 'analyzeSummary']);
		add_action('wp_ajax_SS88MLFS_analyzeList', [$this, 'analyzeList']);
		add_action('wp_ajax_SS88MLFS_analyzeDelete', [$this, 'analyzeDelete']);

	}

	function admin_menu() {

		add_submenu_page(
			'upload.php',
			__('Analyze', 'media-library-file-size'),
			__('Analyze', 'media-library-file-size'),
			'upload_files',
			'ss88-mlfs-analyze',
			[$this, 'renderAnalyzePage'],
			99
		);

	}

	function admin_enqueue_scripts($hook_suffix) {

		if(!isset($_GET['page']) || $_GET['page']!='ss88-mlfs-analyze') return;

		wp_enqueue_style('SS88_MLFS-analyze', plugin_dir_url( __FILE__ ) . 'assets/css/analyze.css', false, $this->version);
		wp_enqueue_script('SS88_MLFS-analyze', plugin_dir_url( __FILE__ ) . 'assets/js/analyze.js', false, $this->version, true);
		wp_localize_script('SS88_MLFS-analyze', 'ss88Analyze', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce('ss88_mlfs_analyze_nonce')
		));

	}

	function renderAnalyzePage() {

		if(!current_user_can('upload_files')) wp_die(esc_html__('Insufficient permissions.', 'media-library-file-size'));

		echo '<div class="wrap ss88-analyze-wrap" id="ss88-mlfs-analyze-page">';
			echo '<h1>' . esc_html__('Analyze Media Library', 'media-library-file-size') . '</h1>';
			echo '<p>' . esc_html__('Analyze your library by total storage usage and drill down by media type.', 'media-library-file-size') . '</p>';

			echo '<div class="ss88-analyze-overview">';
				echo '<div class="ss88-analyze-panel ss88-analyze-chart-panel">';
					echo '<h2>' . esc_html__('Media Type Breakdown', 'media-library-file-size') . '</h2>';
					echo '<div class="ss88-analyze-chart-wrap">';
						echo '<svg id="ss88-analyze-pie" viewBox="0 0 240 240" aria-label="' . esc_attr__('Media type size chart', 'media-library-file-size') . '"></svg>';
						echo '<div class="ss88-analyze-empty" id="ss88-analyze-empty">' . esc_html__('No indexed data available yet.', 'media-library-file-size') . '</div>';
					echo '</div>';
				echo '</div>';

				echo '<div class="ss88-analyze-panel ss88-analyze-key-panel">';
					echo '<h2 id="ss88-analyze-summary-title">' . esc_html__('Summary', 'media-library-file-size') . '</h2>';
					echo '<table class="widefat striped ss88-analyze-key-table">';
						echo '<thead><tr><th>' . esc_html__('Type', 'media-library-file-size') . '</th><th>' . esc_html__('Count', 'media-library-file-size') . '</th><th>' . esc_html__('Total Size', 'media-library-file-size') . '</th></tr></thead>';
						echo '<tbody id="ss88-analyze-key-body"><tr><td colspan="3">' . esc_html__('Loading...', 'media-library-file-size') . '</td></tr></tbody>';
					echo '</table>';
				echo '</div>';
			echo '</div>';

			echo '<div class="ss88-analyze-panel ss88-analyze-list-panel">';
				echo '<h2 id="ss88-analyze-list-title">' . esc_html__('All Files - Largest Files', 'media-library-file-size') . '</h2>';
				echo '<table class="widefat striped ss88-analyze-files-table">';
					echo '<thead><tr><th>' . esc_html__('Thumbnail', 'media-library-file-size') . '</th><th>' . esc_html__('Name', 'media-library-file-size') . '</th><th>' . esc_html__('Uploaded To', 'media-library-file-size') . '</th><th>' . esc_html__('File Size', 'media-library-file-size') . '</th><th>' . esc_html__('Actions', 'media-library-file-size') . '</th></tr></thead>';
					echo '<tbody id="ss88-analyze-list-body"><tr><td colspan="5">' . esc_html__('No data loaded.', 'media-library-file-size') . '</td></tr></tbody>';
				echo '</table>';
				echo '<div class="ss88-analyze-load-more-wrap">';
					echo '<button type="button" class="button button-primary" id="ss88-analyze-load-more">' . esc_html__('Load More', 'media-library-file-size') . '</button>';
				echo '</div>';
			echo '</div>';
		echo '</div>';

	}

	function analyzeSummary() {

		if(!current_user_can('upload_files')) wp_send_json_error(['error' => 'You need permission to analyze media.']);
		if(!check_ajax_referer('ss88_mlfs_analyze_nonce', 'nonce', false)) wp_send_json_error(['error' => 'Security check failed.']);

		$cacheKey = $this->getAnalyzeCacheKey('summary');
		$cached = wp_cache_get($cacheKey, 'ss88_mlfs');
		if($cached!==false) wp_send_json_success($cached);

		global $wpdb;

		$sql = "SELECT p.post_mime_type AS mime, COUNT(p.ID) AS total_count,
			SUM(CAST(COALESCE(pm.meta_value, '0') AS UNSIGNED) + CAST(COALESCE(pmv.meta_value, '0') AS UNSIGNED)) AS total_size
			FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta pm ON pm.post_id = p.ID AND pm.meta_key = 'SS88MLFS'
			LEFT JOIN $wpdb->postmeta pmv ON pmv.post_id = p.ID AND pmv.meta_key = 'SS88MLFSV'
			WHERE p.post_type = 'attachment' AND p.post_status = 'inherit'
			GROUP BY p.post_mime_type";

		$rows = $wpdb->get_results($sql, ARRAY_A);
		$categories = $this->getCategories();
		$returnData = [];
		$totalSize = 0;
		$totalCount = 0;

		foreach($categories as $slug=>$meta) {

			$returnData[$slug] = [
				'slug' => $slug,
				'label' => $meta['label'],
				'color' => $meta['color'],
				'count' => 0,
				'size' => 0,
				'size_hr' => size_format(0)
			];

		}

		foreach((array) $rows as $row) {

			$slug = $this->categorizeMime($row['mime']);
			if(!isset($returnData[$slug])) continue;

			$rowCount = intval($row['total_count']);
			$rowSize = intval($row['total_size']);

			$returnData[$slug]['count'] += $rowCount;
			$returnData[$slug]['size'] += $rowSize;
			$totalCount += $rowCount;
			$totalSize += $rowSize;

		}

		foreach($returnData as $slug=>$data) {

			$returnData[$slug]['size_hr'] = size_format($data['size']);
			if($data['count']==0 && $data['size']==0) unset($returnData[$slug]);

		}

		$returnData = array_values($returnData);
		usort($returnData, function($a, $b) {

			return $b['size'] - $a['size'];

		});

		$responseData = [
			'categories' => $returnData,
			'total_count' => $totalCount,
			'total_size' => $totalSize,
			'total_size_hr' => size_format($totalSize)
		];

		wp_cache_set($cacheKey, $responseData, 'ss88_mlfs', 120);
		wp_send_json_success($responseData);

	}

	function analyzeList() {

		if(!current_user_can('upload_files')) wp_send_json_error(['error' => 'You need permission to analyze media.']);
		if(!check_ajax_referer('ss88_mlfs_analyze_nonce', 'nonce', false)) wp_send_json_error(['error' => 'Security check failed.']);

		$category = (isset($_REQUEST['category'])) ? sanitize_key($_REQUEST['category']) : '';
		$categories = $this->getCategories();
		if(empty($category)) wp_send_json_error(['error' => 'Invalid category.']);
		if($category!='all' && !isset($categories[$category])) wp_send_json_error(['error' => 'Invalid category.']);
		$label = ($category=='all') ? __('All Files', 'media-library-file-size') : $categories[$category]['label'];

		$offset = (isset($_REQUEST['offset'])) ? intval($_REQUEST['offset']) : 0;
		$limit = (isset($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : 25;
		if($offset<0) $offset = 0;
		if($limit<1) $limit = 25;
		if($limit>200) $limit = 200;
		$fetchLimit = $limit + 1;

		$cacheKey = $this->getAnalyzeCacheKey('list_' . $category . '_' . $offset . '_' . $limit);
		$cached = wp_cache_get($cacheKey, 'ss88_mlfs');
		if($cached!==false) wp_send_json_success($cached);

		global $wpdb;
		$sqlSelect = "SELECT p.ID, p.post_parent, p.post_title, p.post_mime_type,
			(CAST(COALESCE(pm.meta_value, '0') AS UNSIGNED) + CAST(COALESCE(pmv.meta_value, '0') AS UNSIGNED)) AS total_size
			FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta pm ON pm.post_id = p.ID AND pm.meta_key = 'SS88MLFS'
			LEFT JOIN $wpdb->postmeta pmv ON pmv.post_id = p.ID AND pmv.meta_key = 'SS88MLFSV'
			WHERE p.post_type = 'attachment' AND p.post_status = 'inherit'";

		switch($category) {
			case 'image':
				$sql = $sqlSelect . " AND p.post_mime_type LIKE 'image/%' ORDER BY total_size DESC, p.ID DESC LIMIT %d OFFSET %d";
				break;
			case 'video':
				$sql = $sqlSelect . " AND (p.post_mime_type LIKE 'video/%' OR p.post_mime_type = 'text/vtt') ORDER BY total_size DESC, p.ID DESC LIMIT %d OFFSET %d";
				break;
			case 'audio':
				$sql = $sqlSelect . " AND p.post_mime_type LIKE 'audio/%' ORDER BY total_size DESC, p.ID DESC LIMIT %d OFFSET %d";
				break;
			case 'archive':
				$sql = $sqlSelect . " AND p.post_mime_type IN ('application/zip','application/x-zip-compressed','application/x-rar-compressed','application/x-7z-compressed','application/x-tar','application/gzip','application/x-gzip','application/x-bzip2') ORDER BY total_size DESC, p.ID DESC LIMIT %d OFFSET %d";
				break;
			case 'document':
				$sql = $sqlSelect . " AND ((p.post_mime_type LIKE 'application/%' OR p.post_mime_type LIKE 'text/%') AND p.post_mime_type NOT IN ('application/zip','application/x-zip-compressed','application/x-rar-compressed','application/x-7z-compressed','application/x-tar','application/gzip','application/x-gzip','application/x-bzip2') AND p.post_mime_type NOT LIKE 'audio/%' AND p.post_mime_type NOT LIKE 'video/%' AND p.post_mime_type != 'text/vtt') ORDER BY total_size DESC, p.ID DESC LIMIT %d OFFSET %d";
				break;
			case 'other':
				$sql = $sqlSelect . " AND (p.post_mime_type NOT LIKE 'image/%' AND p.post_mime_type NOT LIKE 'video/%' AND p.post_mime_type NOT LIKE 'audio/%' AND p.post_mime_type NOT LIKE 'application/%' AND p.post_mime_type NOT LIKE 'text/%') ORDER BY total_size DESC, p.ID DESC LIMIT %d OFFSET %d";
				break;
			default:
				$sql = $sqlSelect . " ORDER BY total_size DESC, p.ID DESC LIMIT %d OFFSET %d";
				break;
		}
		$rows = $wpdb->get_results($wpdb->prepare($sql, $fetchLimit, $offset), ARRAY_A);
		$hasMore = (count((array) $rows)>$limit);
		if($hasMore) $rows = array_slice($rows, 0, $limit);

		$returnRows = [];
		foreach((array) $rows as $row) {

			$attachment_id = intval($row['ID']);
			$sizeBytes = intval($row['total_size']);
			if(empty($sizeBytes)) {

				$sizeBytes = intval(get_post_meta($attachment_id, 'SS88MLFS', true)) + intval(get_post_meta($attachment_id, 'SS88MLFSV', true));

			}

			$thumb = wp_get_attachment_image_src($attachment_id, [60, 60], true);
			$thumbURL = ($thumb && isset($thumb[0])) ? $thumb[0] : wp_mime_type_icon($attachment_id);
			$postParent = intval($row['post_parent']);
			$uploadedTo = ($postParent) ? get_the_title($postParent) : __('(Unattached)', 'media-library-file-size');
			if(empty($uploadedTo)) $uploadedTo = __('(No title)', 'media-library-file-size');

			$name = $row['post_title'];
			if(empty($name)) {

				$file = get_attached_file($attachment_id);
				$name = ($file) ? wp_basename($file) : __('(no name)', 'media-library-file-size');

			}

			$returnRows[] = [
				'id' => $attachment_id,
				'thumbnail' => esc_url_raw($thumbURL),
				'name' => $name,
				'uploaded_to' => $uploadedTo,
				'size' => $sizeBytes,
				'size_hr' => size_format($sizeBytes),
				'view_url' => esc_url_raw(wp_get_attachment_url($attachment_id)),
				'edit_url' => esc_url_raw(get_edit_post_link($attachment_id, '')),
				'can_delete' => current_user_can('delete_post', $attachment_id)
			];

		}

		$responseData = [
			'category' => $category,
			'label' => $label,
			'rows' => $returnRows,
			'has_more' => $hasMore,
			'next_offset' => $offset + count($returnRows)
		];

		wp_cache_set($cacheKey, $responseData, 'ss88_mlfs', 120);
		wp_send_json_success($responseData);

	}

	function analyzeDelete() {

		if(!current_user_can('upload_files')) wp_send_json_error(['error' => 'You need permission to delete media.']);
		if(!check_ajax_referer('ss88_mlfs_analyze_nonce', 'nonce', false)) wp_send_json_error(['error' => 'Security check failed.']);

		$attachment_id = (isset($_POST['attachment_id'])) ? intval($_POST['attachment_id']) : 0;
		if(empty($attachment_id) || get_post_type($attachment_id)!='attachment') wp_send_json_error(['error' => 'Attachment not found.']);
		if(!current_user_can('delete_post', $attachment_id)) wp_send_json_error(['error' => 'You cannot delete this attachment.']);

		$deleted = wp_delete_attachment($attachment_id, true);
		if(empty($deleted)) wp_send_json_error(['error' => 'Delete failed.']);
		$this->bumpAnalyzeCacheVersion();

		wp_send_json_success(['attachment_id' => $attachment_id]);

	}

	function getCategories() {

		return [
			'image' => ['label' => __('Images', 'media-library-file-size'), 'color' => '#2f6fef'],
			'video' => ['label' => __('Videos', 'media-library-file-size'), 'color' => '#f25c54'],
			'audio' => ['label' => __('Audio', 'media-library-file-size'), 'color' => '#17a773'],
			'document' => ['label' => __('Documents', 'media-library-file-size'), 'color' => '#f59e0b'],
			'archive' => ['label' => __('Archives', 'media-library-file-size'), 'color' => '#8b5cf6'],
			'other' => ['label' => __('Other', 'media-library-file-size'), 'color' => '#6b7280']
		];

	}

	function getArchiveMimes() {

		return [
			'application/zip',
			'application/x-zip-compressed',
			'application/x-rar-compressed',
			'application/x-7z-compressed',
			'application/x-tar',
			'application/gzip',
			'application/x-gzip',
			'application/x-bzip2'
		];

	}

	function getImageMimes() {

		return [
			'image/png',
			'image/jpeg',
			'image/gif',
			'image/webp',
			'image/heic',
			'image/heif',
			'image/svg+xml'
		];

	}

	function getAudioMimes() {

		return [
			'audio/mpeg',
			'audio/mp3',
			'audio/ogg',
			'audio/wav',
			'audio/x-wav'
		];

	}

	function getVideoMimes() {

		return [
			'video/mp4',
			'video/x-m4v',
			'video/mpeg',
			'video/quicktime',
			'text/vtt',
			'video/x-msvideo',
			'video/ogg',
			'video/x-ms-wmv',
			'video/3gpp',
			'video/3gpp2'
		];

	}

	function getDocumentMimes() {

		return [
			'application/pdf',
			'application/msword',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/vnd.ms-excel',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'application/vnd.apple.keynote',
			'application/vnd.oasis.opendocument.text',
			'text/plain',
			'application/vnd.ms-powerpoint',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'application/xml',
			'text/xml',
			'text/csv',
			'application/csv'
		];

	}

	function categorizeMime($mime) {

		$mime = strtolower((string) $mime);
		$images = $this->getImageMimes();
		$videos = $this->getVideoMimes();
		$audio = $this->getAudioMimes();
		$documents = $this->getDocumentMimes();
		$archives = $this->getArchiveMimes();

		if(in_array($mime, $images, true)) return 'image';
		if(in_array($mime, $videos, true)) return 'video';
		if(in_array($mime, $audio, true)) return 'audio';
		if(strpos($mime, 'image/')===0) return 'image';
		if(strpos($mime, 'video/')===0) return 'video';
		if(strpos($mime, 'audio/')===0) return 'audio';
		if(in_array($mime, $archives, true)) return 'archive';
		if(in_array($mime, $documents, true)) return 'document';
		if(strpos($mime, 'application/')===0 || strpos($mime, 'text/')===0) return 'document';
		return 'other';

	}

	function getAnalyzeCacheVersion() {

		return intval(get_option('ss88_mlfs_analyze_cache_version', 1));

	}

	function bumpAnalyzeCacheVersion() {

		$Version = $this->getAnalyzeCacheVersion() + 1;
		update_option('ss88_mlfs_analyze_cache_version', $Version, false);

	}

	function getAnalyzeCacheKey($suffix) {

		return 'analyze_v' . $this->getAnalyzeCacheVersion() . '_' . $suffix;

	}

	function getCategoryCondition($category) {

		$images = $this->getImageMimes();
		$videos = $this->getVideoMimes();
		$audio = $this->getAudioMimes();
		$documents = $this->getDocumentMimes();
		$archives = $this->getArchiveMimes();
		$imagePH = implode(',', array_fill(0, count($images), '%s'));
		$videoPH = implode(',', array_fill(0, count($videos), '%s'));
		$audioPH = implode(',', array_fill(0, count($audio), '%s'));
		$documentPH = implode(',', array_fill(0, count($documents), '%s'));
		$archivePH = implode(',', array_fill(0, count($archives), '%s'));

		switch($category) {
			case 'image':
				return ['sql' => "(p.post_mime_type IN ($imagePH) OR p.post_mime_type LIKE 'image/%')", 'args' => $images];
			case 'video':
				return ['sql' => "(p.post_mime_type IN ($videoPH) OR p.post_mime_type LIKE 'video/%')", 'args' => $videos];
			case 'audio':
				return ['sql' => "(p.post_mime_type IN ($audioPH) OR p.post_mime_type LIKE 'audio/%')", 'args' => $audio];
			case 'archive':
				return ['sql' => "p.post_mime_type IN ($archivePH)", 'args' => $archives];
			case 'document':
				return ['sql' => "((p.post_mime_type IN ($documentPH) OR p.post_mime_type LIKE 'application/%' OR p.post_mime_type LIKE 'text/%') AND p.post_mime_type NOT IN ($archivePH) AND p.post_mime_type NOT IN ($videoPH) AND p.post_mime_type NOT IN ($audioPH))", 'args' => array_merge($documents, $archives, $videos, $audio)];
			case 'other':
				return ['sql' => "(p.post_mime_type NOT LIKE 'image/%' AND p.post_mime_type NOT LIKE 'video/%' AND p.post_mime_type NOT LIKE 'audio/%' AND p.post_mime_type NOT LIKE 'application/%' AND p.post_mime_type NOT LIKE 'text/%')", 'args' => []];
			case 'all':
				return ['sql' => '1=1', 'args' => []];
		}

		return ['sql' => '1=0', 'args' => []];

	}

}

add_action('plugins_loaded', ['SS88_MediaLibraryFileSize_Analyze', 'init']);
