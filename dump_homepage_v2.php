<?php
require_once('wp-load.php');

$front_page_id = get_option('page_on_front');
echo "Front Page ID: " . $front_page_id . "\n";

$elementor_data = get_post_meta($front_page_id, '_elementor_data', true);

if ($elementor_data) {
    $data = json_decode($elementor_data, true);
    echo "Elementor Data Structure:\n";
    
    function print_elementor_structure($elements, $level = 0) {
        foreach ($elements as $element) {
            echo str_repeat("  ", $level) . "- Type: " . $element['elType'];
            if (isset($element['widgetType'])) {
                echo " (" . $element['widgetType'] . ")";
            }
            
            // Settings
            if (isset($element['settings'])) {
                $settings = $element['settings'];
                
                if (isset($settings['title'])) {
                    echo " Title: " . $settings['title'];
                }
                if (isset($settings['editor'])) {
                    echo " [Text Content Present]";
                     echo "\n" . str_repeat("  ", $level + 1) . "Content: " . substr(strip_tags($settings['editor']), 0, 100) . "...\n";
                }
                if (isset($settings['text'])) { // Sometimes headings have 'text'
                     echo " Text: " . $settings['text'];
                }
                
                if (isset($settings['template_id'])) {
                    echo " Template ID: " . $settings['template_id'];
                }
                
                // Styles
                $style_keys = ['background_color', 'background_image', 'padding', 'margin', 'typography_font_family', 'typography_font_size', 'typography_font_weight'];
                foreach ($style_keys as $key) {
                    if (isset($settings[$key])) {
                        $val = $settings[$key];
                        if (is_array($val)) $val = json_encode($val);
                        echo "\n" . str_repeat("  ", $level + 1) . "Style [$key]: " . $val;
                    }
                }
            }

            echo "\n";
            
            if (isset($element['elements'])) {
                print_elementor_structure($element['elements'], $level + 1);
            }
        }
    }
    
    // print_elementor_structure($data);
    
    // Dump specific template
    $template_id = 37161;
    echo "\n\nTemplate ID: $template_id\n";
    $template_data = get_post_meta($template_id, '_elementor_data', true);
    if ($template_data) {
        $data = json_decode($template_data, true);
        print_elementor_structure($data);
    } else {
        echo "No data found for template $template_id\n";
    }
} else {
    echo "No Elementor Data found.\n";
}
