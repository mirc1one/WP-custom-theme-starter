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

// custom check caching queries, if the key-identifier combination was not set,
// it caches the item then returns it, if it was set it just reads it from the cache
function custom_cache($key, $callback) {
    $get_cache = wp_cache_get($key, '');

    if (empty($get_cache)) {
        $query = $callback();
        wp_cache_add($key, maybe_serialize($query), '', 3600);

        return $query;
    }
    
    return unserialize($get_cache);
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
