<?php
/**
 * Plugin Name: LHG Activity Plugin
 * Plugin URI: https://www.lhgraphics.com/lhg-activity-plugin
 * Description: A WordPress plugin with Activity operations and settings using MVC pattern
 * Version: 1.0.0
 * Author: Light House Graphics
 * Author URI: https://www.lhgraphics.com/
 * Text Domain: lhg-activity-plugin
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('LHG_ACTIVITY_PLUGIN_VERSION', '1.0.0');
define('LHG_ACTIVITY_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LHG_ACTIVITY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LHG_ACTIVITY_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include the core plugin class
require_once LHG_ACTIVITY_PLUGIN_PATH . 'includes/class-lhg-activity-plugin.php';
require_once plugin_dir_path(__FILE__) . 'controllers/class-lhg-activity-plugin-admin-controller.php';


// Activation and deactivation hooks
register_activation_hook(__FILE__, array('LHG_ACTIVITY_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('LHG_ACTIVITY_Plugin', 'deactivate'));


// Initialize the plugin
function run_lhg_activity_plugin() {
    $plugin = new LHG_ACTIVITY_Plugin();
    $plugin->run();
}
run_lhg_activity_plugin();

function add_view_log_button($actions, $post) {
    $log_url = admin_url('admin.php?page=lhg-activity-plugin&post_id=' . $post->ID);
    $actions['view_log'] = '<a href="' . esc_url($log_url) . '" target="_blank" rel="noopener noreferrer">View Log</a>';
    return $actions;
}

add_filter('post_row_actions', 'add_view_log_button', 10, 2);
add_filter('page_row_actions', 'add_view_log_button', 10, 2);



// Function for taxonomy terms
function add_view_log_button_to_taxonomy($actions, $tag) {
    $log_url = admin_url('admin.php?page=lhg-activity-plugin&post_id=' . $tag->term_id);
    $actions['view_log'] = '<a href="' . esc_url($log_url) . '" target="_blank" rel="noopener noreferrer">View Log</a>';
    return $actions;
}
// Add filter for default categories
add_filter('tag_row_actions', 'add_view_log_button_to_taxonomy', 10, 2);
add_filter('your_custom_taxonomy_row_actions', 'add_view_log_button_to_taxonomy', 10, 2);


function add_view_log_button_to_plugins($actions, $plugin_file) {
    $plugin_slug = explode('/', $plugin_file)[0];
    $log_url = admin_url('admin.php?page=lhg-activity-plugin&filter_page_detail=' . urlencode($plugin_slug));
    $actions['view_log'] = '<a href="' . esc_url($log_url) . '" target="_blank" rel="noopener noreferrer">View Log</a>';
    return $actions;
}
// Add filter for plugins listing page
add_filter('plugin_action_links', 'add_view_log_button_to_plugins', 10, 2);




