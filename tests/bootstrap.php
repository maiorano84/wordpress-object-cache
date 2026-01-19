<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/class-wp-object-cache.php';

define('ABSPATH', __DIR__ . '/..');

define('WPINC', '/tests');

if (!function_exists('wp_suspend_cache_addition')) {
    function wp_suspend_cache_addition()
    {
        return false;
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters(string $hook, mixed $value)
    {
        return $value;
    }
}