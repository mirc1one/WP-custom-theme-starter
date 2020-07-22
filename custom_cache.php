<?php

// custom check caching queries, if the key-identifier combination was not set,
// it caches the item then returns it, if it was set it just reads it from the cache
function custom_cache($key, $group, $callback, $lifespan = HOUR_IN_SECONDS) {
    $get_cache = wp_cache_get($key, $group);
    
    if (empty($get_cache)) {
        $query = $callback();
        wp_cache_add($key, maybe_serialize($query), $group, $lifespan);

        return $query;
    }
    
    return maybe_unserialize($get_cache);
}

// transients, this stores it in the database, or if Object Cache is activated in the server
// if wordpress decides, it could just store it in the memory
function custom_transient($key, $group, $callback, $lifespan = DAY_IN_SECONDS) {
    $get_transient = get_transient($group . '_' . $key);
    
    if (empty($get_transient)) {
        $query = $callback();
        set_transient($group . '_' . $key, $query, $lifespan);

        return $query;
    }
    
    return maybe_unserialize($get_transient);
}

function delete_custom_transient($key, $group) {
    $get_transient = get_transient($group . '_' . $key);

    if (!empty($get_transient)) {
        delete_transient($group . '_' . $key);

        return true;
    }

    return false;
}

// adding actions when updating or inserting a new post
add_action('save_post', 'hook_on_insert_post', 10, 2);
function hook_on_insert_post($post_id, $post) {
    // check if it's the real post and not a revision
    $is_revision = wp_is_post_revision($post);

    if ($is_revision) {
        if (get_post_type($is_revision) === 'contest') {
            delete_custom_transient('results_query', 'widget_sidebar');
            delete_custom_transient('upcoming_query', 'widget_sidebar');
        }
    } else {
        if ($post->post_type === 'contest') {
            delete_custom_transient('results_query', 'widget_sidebar');
            delete_custom_transient('upcoming_query', 'widget_sidebar');
        }
    }
}

add_action('post_updated', 'hook_on_contest_update', 10, 2);
function hook_on_contest_update($post_id, $post) {
    if ($post->post_type === 'contest') {
        delete_custom_transient('results_query', 'widget_sidebar');
        delete_custom_transient('upcoming_query', 'widget_sidebar');
    }
}