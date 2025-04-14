<?php
/**
 * The core plugin class.
 */
class LHG_ACTIVITY_Plugin {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     */
    protected $loader;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        // The class responsible for orchestrating the actions and filters
        require_once LHG_ACTIVITY_PLUGIN_PATH . 'includes/class-lhg-activity-plugin-loader.php';
        
        // The class responsible for defining all actions for the admin area
        require_once LHG_ACTIVITY_PLUGIN_PATH . 'admin/class-lhg-activity-plugin-admin.php';
        
        // Load model classes
        require_once LHG_ACTIVITY_PLUGIN_PATH . 'models/class-lhg-activity-plugin-item-model.php';
        
        // Load controller classes
        require_once LHG_ACTIVITY_PLUGIN_PATH . 'controllers/class-lhg-activity-plugin-item-controller.php';
        
        $this->loader = new LHG_ACTIVITY_Plugin_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new LHG_ACTIVITY_Plugin_Admin();
        
        // Add admin menu
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        
        // Add settings link to plugins page
        $this->loader->add_filter('plugin_action_links_' . LHG_ACTIVITY_PLUGIN_BASENAME, $plugin_admin, 'add_action_links');
        
        // Register settings
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        
        // Enqueue styles and scripts
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // AJAX handlers
        $this->loader->add_action('wp_ajax_lhg_activity_add_item', $plugin_admin, 'ajax_add_item');
        $this->loader->add_action('wp_ajax_lhg_activity_update_item', $plugin_admin, 'ajax_update_item');
        $this->loader->add_action('wp_ajax_lhg_activity_delete_item', $plugin_admin, 'ajax_delete_item'); 

        // export 
        $this->loader->add_action('wp_ajax_lhg_activity_export_to_excel', $plugin_admin,'export_to_excel');
        $this->loader->add_action('wp_ajax_lhg_activity_save_elementor_log', $plugin_admin,'save_elementor_log');
        //$this->loader->add_action('wp_ajax_nopriv_lhg_activity_export_to_excel', $plugin_admin,'export_to_excel'); // For non-logged-in users (optional)

        // post 
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin,'custom_enqueue_admin_script'); // For non-logged-in users (optional)
   
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * Plugin activation.
     */
    public static function activate() {
        require_once LHG_ACTIVITY_PLUGIN_PATH . 'includes/class-lhg-activity-plugin-activator.php';
        LHG_ACTIVITY_Plugin_Activator::activate();
    }

    /**
     * Plugin deactivation.
    */

    public static function deactivate() {
        require_once LHG_ACTIVITY_PLUGIN_PATH . 'includes/class-lhg-activity-plugin-deactivator.php';
        LHG_ACTIVITY_Plugin_Deactivator::deactivate();
    }
}

