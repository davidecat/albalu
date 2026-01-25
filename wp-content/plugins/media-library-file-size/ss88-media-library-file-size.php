<?php
/*
Plugin Name: Media Library File Size
Plugin URI: https://ss88.us/plugins/media-library-file-size?utm_source=wordpress&utm_medium=link&utm_campaign=mlfs
Description: Creates a new column in your Media Library to show you the file (and collective images) size of files plus more!
Version: 1.6.7
Author: SS88 LLC
Author URI: https://ss88.us/?utm_source=wordpress&utm_medium=link&utm_campaign=author_mlfs
Text Domain: media-library-file-size
*/

class SS88_MediaLibraryFileSize {

    protected $version = '1.6.7';
	protected $variantJSON = [];

    public static function init() {

        $C = __CLASS__;
        new $C;

    }

    function __construct() {

        global $pagenow;

		register_uninstall_hook(__FILE__, ['SS88_MediaLibraryFileSize', 'register_uninstall_hook']);

        if($pagenow=='upload.php') {

            add_filter('manage_media_custom_column', [$this, 'manage_media_custom_column'], 10, 2);
            add_filter('manage_media_columns', [$this, 'manage_media_columns']);
            add_action('manage_upload_sortable_columns', [$this, 'manage_upload_sortable_columns']);
            add_action('pre_get_posts', [$this, 'pre_get_posts']);
            add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
			add_action('admin_footer', [$this, 'admin_footer_view_variants_json']);

        }

        if(is_admin()) {

            add_action('wp_ajax_SS88MLFS_index', [$this, 'index']);
			add_action('wp_ajax_SS88MLFS_indexCount', [$this, 'indexCount']);

        }

		add_filter('wp_generate_attachment_metadata', [$this, 'wp_generate_attachment_metadata'], PHP_INT_MAX, 2);
		add_filter('wp_update_attachment_metadata', [$this, 'wp_generate_attachment_metadata'], PHP_INT_MAX, 2);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'plugin_action_links']);
		add_filter('plugin_row_meta', [$this, 'plugin_row_meta'], 10, 4);

		add_action('activated_plugin', [$this, 'activated_plugin']);

    }

	public static function activated_plugin($plugin) {

		if($plugin == plugin_basename(__FILE__)) {

			wp_safe_redirect(admin_url('upload.php?mode=list&ss88first'));
			exit;

		}

	}

    function plugin_action_links($actions) {

        $mylinks = [
            '<a href="https://wordpress.org/support/plugin/media-library-file-size/" target="_blank">Need help?</a>',
        ];

        return array_merge( $actions, $mylinks );

    }

	function plugin_row_meta($plugin_meta, $plugin_file, $plugin_data, $status) {

		if ($plugin_file ==  plugin_basename(__FILE__)) {

			$plugin_meta[] = '<a href="https://wordpress.org/support/plugin/media-library-file-size/reviews/" target="_blank">Like? ⭐️ Rate!</a>';
		
		}

		return $plugin_meta;

	}

    function admin_enqueue_scripts() {

        wp_enqueue_script('noty', plugin_dir_url( __FILE__ ) . 'assets/js/noty.js', false, $this->version, true);
        wp_enqueue_script('SS88_MLFS-media', plugin_dir_url( __FILE__ ) . 'assets/js/media.js', ['noty'], $this->version, true);
        wp_localize_script('SS88_MLFS-media', 'ss88', array('ajax_url' => admin_url( 'admin-ajax.php' )));

        wp_enqueue_style('noty', plugin_dir_url( __FILE__ ) . 'assets/css/noty.css', false, $this->version);
        wp_enqueue_style('SS88_MLFS-media', plugin_dir_url( __FILE__ ) . 'assets/css/media.css', false, $this->version);

    }

    function index() {

        set_time_limit(600);
        ini_set('max_execution_time', 600);

        $returnData = [];
		$reindexMedia = (isset($_POST['reindex']) && $_POST['reindex'] == true) ? true : false;
        $attachmentsPerBatch = 100;
        $attachmentsPaged = 1;
        $attachmentProcessed = 0;
        $noAttachments = false;

        do {

            $args = [
                'post_type' => 'attachment',
                'posts_per_page' => $attachmentsPerBatch,
                'paged' => $attachmentsPaged,
                'meta_query' => [
                    'relation' => 'OR',
                    [
                        'key' => 'SS88MLFS',
                        'compare' => 'NOT EXISTS'
                    ],
                    [
                        'key' => 'SS88MLFSV',
                        'compare' => 'NOT EXISTS'
                    ]
                ]
            ];
    
            if($reindexMedia) {

                unset($args['meta_query']);
    
            }
    
            $attachments = get_posts($args);
            if (empty($attachments)) {
                
                $noAttachments = true;
                break;

            }

            foreach($attachments as $attachment) {

                $metadata = wp_get_attachment_metadata($attachment->ID);

                if($this->updateSize($metadata, $attachment->ID)) {

                    $attachmentProcessed++;
					if($attachmentProcessed>999) continue;

                    $returnData[] = [
                        'attachment_id' => $attachment->ID,
                        'html' => $this->outputHTML($attachment->ID)
                    ];
        
                }

            }
    
            $attachmentsPaged++;
    
        } while (count($attachments) === $attachmentsPerBatch);

        if($noAttachments) {

            wp_send_json_error(['httpcode' => -1, 'body' => 'There are no attachments to index.']);

        }

        if($attachmentProcessed) {

            $attachmentProcessed = number_format($attachmentProcessed);
            $finalMessage = 'You just indexed '. $attachmentProcessed .' attachments. Your media library has been indexed.';
            if($reindexMedia) $finalMessage = 'You just reindexed '. $attachmentProcessed .' attachments.';
            
            wp_send_json_success([
                'html' => $returnData,
                'message' => $finalMessage
            ]);
        
        }
        else wp_send_json_error(['httpcode' => 99, 'body' => 'No attachments were indexed. This usually means they exist, but the file(s) are not on the local server.']);

    }

	function indexCount() {

		global $wpdb;

		$TotalMLSize = $wpdb->get_var("SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = 'SS88MLFS'");
		$TotalMLSizeV = $wpdb->get_var("SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = 'SS88MLFSV'");
		$SpanTitle = ($TotalMLSizeV) ? size_format($TotalMLSize, 2) . ' + ' . size_format($TotalMLSizeV, 2) . '<br>of variants' : '';

		$ReturnData = ['TotalMLSize' => size_format($TotalMLSize + $TotalMLSizeV), 'TotalMLSize_Title' => $SpanTitle];

		if($TotalMLSize || $TotalMLSizeV) wp_send_json_success($ReturnData);
		else return wp_send_json_error($ReturnData);

	}

    function wp_generate_attachment_metadata($data, $attachment_id) {

        $this->updateSize($data, $attachment_id);

        return $data;

    }

    function manage_upload_sortable_columns($columns) {

        $columns['SS88_MediaLibraryFileSize'] = 'SS88_MediaLibraryFileSize';
        
        return $columns;

    }

	function manage_media_columns($columns) {

		$columns['SS88_MediaLibraryFileSize'] = __('File Size', 'media-library-file-size');
		
		return $columns;
	
	}

	function manage_media_custom_column($columnName, $postID) {

        if($columnName == 'SS88_MediaLibraryFileSize') {

			echo wp_kses_post( $this->outputHTML($postID) );

        }

	}

    function pre_get_posts($query) {

        if(!empty($_REQUEST['orderby']) && $_REQUEST['orderby'] == 'SS88_MediaLibraryFileSize') {

            $query->set('order', (isset($_REQUEST['order']) && $_REQUEST['order']=='asc') ? 'asc' : 'desc');
            $query->set('orderby', 'meta_value_num');
            $query->set('meta_key', 'SS88MLFS');

        }

    }

    function updateSize($data, $attachment_id) {

        $Size = 0;
		$File = get_attached_file($attachment_id);

        if(isset($data['filesize'])) {

            $Size = $data['filesize'];

        }

        if($Size===0 && file_exists($File)) {

            $Size = filesize($File);

        }

        if($Size) {

            update_post_meta($attachment_id, 'SS88MLFS', $Size);
			update_post_meta($attachment_id, 'SS88MLFSV', $this->getVariantSize($attachment_id));

        }

        return $Size;

    }

    function outputHTML($attachment_id) {

        $html = '';

        $file = get_attached_file($attachment_id);
        $Variants = wp_get_attachment_metadata($attachment_id);
        $VariantSize = $this->getVariantSize($attachment_id);

        $ExtaHTML = ($VariantSize) ? '<small>(+'. size_format($VariantSize) .')</small>' : '';
        $MetaSize = get_post_meta($attachment_id, 'SS88MLFS', true);
        $FinalSize = isset($Variants['filesize']) ? $Variants['filesize'] : $MetaSize;
		$ViewVariants = (isset($Variants['sizes']) && count($Variants['sizes'])>0) ? '<button class="ss88MLFS_VV" data-aid="'. $attachment_id .'">View Variants</button>' : '';

        if($FinalSize) {

            $html = size_format($FinalSize) . $ExtaHTML . $ViewVariants;

			if(isset($Variants['sizes'])) {

				$AttachmentURL = wp_get_attachment_url($attachment_id);
				
				foreach($Variants['sizes'] as $v_size=>$v_data) {

					$VSize = (!isset($v_data['filesize'])) ? filesize(pathinfo($file, PATHINFO_DIRNAME) . '/' . $v_data['file']) : $v_data['filesize'];
					$VSize = (empty($VSize)) ? 'Unknown' : $VSize;

					$this->variantJSON[$attachment_id][] = [
						'size' => $v_size,
						'width' => intval($v_data['width']),
						'height' => intval($v_data['height']),
						'filesize_hr' => size_format($VSize),
						'filename' => pathinfo($AttachmentURL, PATHINFO_DIRNAME) . '/' . $v_data['file']
					];

				}
	
			}

        }

        return $html;

    }

	function getVariantSize($attachment_id) {

		if(empty($attachment_id)) return false;

        $file = get_attached_file($attachment_id);
        $Variants = wp_get_attachment_metadata($attachment_id);
        $VariantSize = 0;

        if(isset($Variants['sizes'])) {

            foreach($Variants['sizes'] as $Variant) {

                $VariantSize += isset($Variant['filesize']) ? $Variant['filesize'] : filesize( pathinfo($file, PATHINFO_DIRNAME) . '/' . $Variant['file'] );

            }

        }

		return intval($VariantSize);

	}

	function admin_footer_view_variants_json() {

		echo '<script> const ss88MLFS_VV = '. wp_json_encode($this->variantJSON) .'; </script>';

	}

	public static function register_uninstall_hook() {
		
		delete_post_meta_by_key('SS88MLFS');
		delete_post_meta_by_key('SS88MLFSV');
		
	}

	function debug($msg) {

		error_log("\n" . '[' . gmdate('Y-m-d H:i:s') . '] ' .  $msg, 3, plugin_dir_path(__FILE__) . 'debug.log');

	}

}

add_action('plugins_loaded', ['SS88_MediaLibraryFileSize', 'init']);
add_action('activated_plugin', ['SS88_MediaLibraryFileSize', 'activated_plugin']);