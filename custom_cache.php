<?php

// custom check caching queries, if the key-identifier combination was not set,
// it caches the item then returns it, if it was set it just reads it from the cache
function custom_cache(
    $key, 
    $group, 
    $callback, 
    $lifespan = DAY_IN_SECONDS, 
    $paged = false
) {
    if ($paged === false) $get_cache = wp_cache_get($key, $group);
        else $get_cache = wp_cache_get($key . '_paged_' . $paged, $group);
    
    if (empty($get_cache)) {
        $query = $callback();

        if ($paged === false) wp_cache_add($key, maybe_serialize($query), $group, $lifespan);
            else wp_cache_add($key . '_paged_' . $paged, maybe_serialize($query), $group, $lifespan);

        return $query;
    }
    
    return maybe_unserialize($get_cache);
}

function delete_custom_cache($key, $group = '', $paged = false) {
    if ($paged === false) $get_cache = wp_cache_get($key, $group);
        else $get_cache = wp_cache_get($key . '_paged_' . $paged, $group);

    if (!empty($get_cache)) {
        if ($paged === false) wp_cache_delete($key, $group);
            else wp_cache_delete($key . '_paged_' . $paged, $group);

        return true;
    }
    
    return false;
}

// in order to clear individual contests & fighters tickets cache we must know their ids
// we cannot clear these without knowing their id, and can't call a general function when we need to
// iterate over ids; this function saves in memory over 1hour lifespan all cached tickets
// $key will be the id identifier, $group will be split in 2, one for fighters and one for contests
function custom_cache_query_tickets_ids_tracker($key, $group, $lifespan = HOUR_IN_SECONDS) {
    $get_cache = wp_cache_get('ids_tickets_tracker', $group);
    $get_cache = maybe_unserialize($get_cache);

    if (empty($get_cache)) 
        $get_cache = array();

    if (!in_array($key, $get_cache)) {
        array_push($get_cache, $key);

        wp_cache_set('ids_tickets_tracker', maybe_serialize($get_cache), $group, $lifespan);
    }
}

function delete_pagination_custom_cache($key, $group = '') {
    $get_pagination_keys = get_redis_keys($key, $group);

    if (!empty($get_pagination_keys)) {
        foreach ($get_pagination_keys as $redis_key) {
            $pagination = explode('_paged_', $redis_key);

            if (count($pagination) > 1)
                delete_custom_cache($key, $group, $pagination[1]);
        }
    }
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

// get redis keys, this function was mostly made to get the $paged keys
// that have pagination queries
function get_redis_keys($key, $group = '') {
    // in case the redis plugin doesn't exist
    if (!class_exists('Redis')) 
        return array();

    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);

    if ($group) $wildcard = $group . '.' . $key;
        else $wildcard = $key;

    $keys = $redis->keys('*' . $wildcard . '*');
    $redis->close();

    // it pretty much comes as empty array if it doesn't find any
    // but im gonna leave one extra step to make sure
    return is_array($keys) ? $keys : array();
}
