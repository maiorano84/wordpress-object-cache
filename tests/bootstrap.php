<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load a WP_Object_Cache stub
require_once __DIR__ . '/class-wp-object-cache.php';

// Fake database object
$dbmock = [
    'prefix' => 'wp_',
];
$GLOBALS['wpdb'] = (object)$dbmock;

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

if (!function_exists('is_multisite')) {
    function is_multisite(): bool
    {
        return false;
    }
}

if (!function_exists('get_current_blog_id')) {
    function get_current_blog_id(): int
    {
        return 1;
    }
}