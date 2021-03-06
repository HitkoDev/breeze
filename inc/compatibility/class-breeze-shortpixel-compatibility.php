<?php

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

if (!class_exists('Breeze_Shortpixel_Compatibility')) {
    class Breeze_Shortpixel_Compatibility {
        public function __construct() {
            // on ShortPixel Clear cache.
            add_action('wp_ajax_shortpixel_ai_handle_page_action', [&$this, 'clear_breeze_cache'], 5);

            add_action('init', [&$this, 'schedule_breeze_cache_reset']);

            add_action('breeze_pixel_cache_event', [&$this, 'clear_all_breeze_cache']);
        }

        public function schedule_breeze_cache_reset() {
            // If ShortPixel event is active, we get the next running time.
            $next_timer = wp_next_scheduled('spai_lqip_generate_event');
            if ($next_timer !== false) {
                $next_timer = $next_timer + 30; // add 30 seconds.
                // Clear the the Breeze cache using a single event.
                wp_schedule_single_event($next_timer, 'breeze_pixel_cache_event', []);
            }
        }

        public function clear_breeze_cache() {
            $data = $_POST['data'];

            $action = isset($data['action']) ? $data['action'] : null;
            // Clear LQIP cache and Clear CSS cache.
            if ($action === 'clear lqip cache' || $action === 'clear css cache') {
                $this->clear_all_breeze_cache();
            }
        }

        public function clear_all_breeze_cache() {
            //delete cache after settings
            do_action('breeze_clear_all_cache');
        }
    }

    new Breeze_Shortpixel_Compatibility();
}
