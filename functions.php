<?php

// prevent WP Caching when in development
define('WP_IN_DEVELOPMENT', false); 

if (WP_IN_DEVELOPMENT === true) {
	$theme_version = time();
} else {
	$theme_version = '1.0.0';
}

// used for debugging
function ip_check($check_ip) {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return $ip === $check_ip;
}

function debug(...$value) {
    if (ip_check('YOUR_IP')) {
        if (is_array($value)) {
            foreach ($value as $val) {
                echo "<pre>";
                echo var_dump($val);
                echo "</pre>";
            }
        } else {
            echo "<pre>";
            echo var_dump($val);
            echo "</pre>";
        }
    }
}

// Images
function print_if_alt_params($alt) {
    return (!empty($alt) ? 'alt="' . $alt . '"' : null);
}

// if it is a GIF image we must output the 'full' size, because the animation won't work
function get_file_extension($file_name) {
    $splitter = explode('.', $file_name);
	
    return strtolower($splitter[count($splitter) - 1]);
}

function print_image_src($image, $size) {
    $extension = get_file_extension($image['filename']);
	
    if ($extension === 'gif') {
        return $image['url'];
    }

    return $image['sizes'][$size];
}

function get_file_type($extension) {
    switch ($extension) :
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return 'image';

        case 'mp4':
            return 'video';
    endswitch;

    return 'unknown';
}

// remove pesky unwanted paragraphs
// mostly used in conjunction with wpautop for headlines
// when they require some styling like italics, bold or underlines
function remove_paragraphs($string, $brs = true) {
	if (!$brs) 
		return strip_tags($string, '<b><strong><i><em><u><a>');
	
	return strip_tags($string, '<b><strong><br><i><em><u><a>');
}

// Get post attached featured image
function get_featured_image_alt($post_id) {
	return get_post_meta(
		get_post_thumbnail_id($post_id), 
		'_wp_attachment_image_alt', 
		true
	); 
}

// a function that lets you add multiple actions at once with the same callback
function add_actions($actions, $function_to_add, $priority = 10, $args = 1) {

    $actions = explode(' ', $actions);
    
    foreach ($actions as $action) {
        add_action($action, $function_to_add, $priority, $args);
    }

}
