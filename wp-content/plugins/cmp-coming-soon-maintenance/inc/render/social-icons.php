<?php 
$html = '';
$ulclass = ( $ulclass != '' ) ? ' ' . $ulclass : $ulclass;
$liclass = ( $liclass != '' ) ? ' ' . $liclass : $liclass;

// migrate social media to new option after update 1.4.0
if ( get_option('niteoCS_socialmedia') ) {

    $socialmedia = stripslashes( get_option('niteoCS_socialmedia') );
    $socialmedia = json_decode( $socialmedia, true );
    $socialmedia = is_array( $socialmedia ) ? $socialmedia : array();
    //sort social icons array by hidden, then order key
    uasort( $socialmedia  , array($this,'sort_social') );

    $theme_html = ( $themeslug == 'stylo' || $themeslug == 'mosaic' || $themeslug == 'saturn') ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="3em" height="3em" viewBox="0 0 80 80" xml:space="preserve"><circle transform="rotate(-90 40 40)" class="another-circle" cx="40" cy="40" r="36" /></svg>' : '';
    
    if ( $title == true ) {
        $soc_title = $this->cmp_wpml_translate_string( stripslashes( get_option('niteoCS_soc_title', 'GET SOCIAL WITH US') ), 'Social Icons Title' );;

        $html = ( $soc_title == '' ) ? '' : '<h2 class="soc-title">' . esc_html( $soc_title ) . '</h2>';
    }

    ob_start();

    foreach ( $socialmedia as $social ) {

        if ( !is_array( $social ) || !isset( $social['name'], $social['url'], $social['hidden'], $social['active'] ) ) {
            continue;
        }

        $social_key = sanitize_key( $social['name'] );

        if ( $social['hidden'] === '0' && $social['active'] === '1' && $social_key !== '' ) {
            $href = $social['url'];
            $name = ucfirst($social_key);
            $icon = 'fab fa-' . $social_key;

            switch ($social_key) {
                case 'envelope-o':
                    $href = 'mailto:'.$social['url'];
                    $name = __('Email', 'cmp-coming-soon-maintenance');
                    $icon = 'far fa-envelope';
                    break;
                case 'phone':
                    $href = 'tel:'.$social['url'];
                    $name = __('Phone', 'cmp-coming-soon-maintenance');
                    $icon = 'fas fa-phone';
                    break;
                case 'whatsapp':
                    $name = 'WhatsApp';
                    $href = 'https://wa.me/'.preg_replace('/[^0-9]/', '', $social['url']);
                    break;
                case 'linkedin':
                    $name = 'LinkedIn';
                    break;
                case 'deviantart':
                    $name = 'DeviantArt';
                    break;
                case 'soundcloud':
                    $name = 'SoundCloud';
                    break;
                case 'vk':
                    $name = 'VK';
                    break;
                case 'github':
                    $name = 'GitHub';
                    break;
                case 'tiktok':
                    $name = 'TikTok';
                    break;
                case 'rss':
                    $icon = 'fas fa-rss';
                    $href = $href ? $href : get_bloginfo('rss2_url');
                    break;
                case 'wikipedia':
                    $icon = 'fab fa-wikipedia-w';
                    break;
                case 'x':
                    $icon = 'fab fa-x-twitter';
                    break;
                default:
                    break;
            } 

            echo ( $mode == 'text' ) ? 
                '<li class="social-child' . esc_attr( $liclass ) . '"><a href="'.esc_url($href).'" target="top" class="social-'.esc_attr($social_key).'">'.esc_html($name).'</a></li>' : 
                '<li class="social-child' . esc_attr( $liclass ) . '"><a href="'.esc_url($href).'" target="top" class="social-'.esc_attr($social_key).'">'.$theme_html.'<i class="'.esc_attr($icon).'" aria-hidden="true"></i></a></li>';
        } 
    }

    $social_list = ob_get_clean();

    if ( $social_list != '' ) {
        $html = $html . '<ul class="social-list' . $ulclass . '">' . $social_list.'</ul>';
    }
}