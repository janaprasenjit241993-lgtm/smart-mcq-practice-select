<?php
/**
 * Plugin Name: Smart MCQ Practice Select
 * Description: MCQ practice plugin that loads questions from uploaded CSV chapter files.
 * Version: 1.0.0
 * Author: Smart MCQ
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SMART_MCQ_PLUGIN_FILE', __FILE__ );
define( 'SMART_MCQ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SMART_MCQ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once SMART_MCQ_PLUGIN_DIR . 'includes/Smart_MCQ_CSV_Loader.php';
require_once SMART_MCQ_PLUGIN_DIR . 'includes/ajax-handler.php';

function smart_mcq_enqueue_assets() {
    wp_enqueue_script(
        'smart-mcq-frontend',
        SMART_MCQ_PLUGIN_URL . 'assets/js/frontend.js',
        array(),
        '1.0.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'smart_mcq_enqueue_assets' );
