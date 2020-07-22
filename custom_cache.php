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
