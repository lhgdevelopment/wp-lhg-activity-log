<?php
class LHG_Activity_Plugin_Admin_Controller {
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('save_post', array($this, 'save_additional_info'));
        add_action('user_register', array($this, 'save_user_creation_info'));
        add_action('elementor/editor/after_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        // post category        
        add_action('created_category', array($this, 'save_custom_category_field'));
        add_action('edited_category', array($this, 'save_custom_category_field'));
        // post tag
        add_action('edited_post_tag', array($this, 'save_custom_category_field'));
        add_action('create_post_tag', array($this, 'save_custom_category_field'));

        // taxonomy 
        add_action('init', array($this, 'my_dynamic_taxonomy_create_update_action'));
        
    }

    function my_dynamic_taxonomy_create_update_action() {
        // Get all public taxonomies
        //$taxonomies = get_taxonomies(array('public' => true), 'names');
        $taxonomies = get_taxonomies(array('public' => true, '_builtin' => false), 'names');
        foreach ($taxonomies as $taxonomy) {
            add_action("create_{$taxonomy}", array($this, 'save_custom_category_field'), 10, 2);
            add_action("edited_{$taxonomy}", array($this, 'save_custom_category_field'), 10, 2);
        }
    }

    /*public function enqueue_admin_scripts($hook) {
        // Load only on the post editor screen
        $screen = get_current_screen();
        if (in_array($screen->base, ['post', 'edit', 'edit-tags', 'term']) || \Elementor\Plugin::instance()->editor->is_edit_mode()) {
            wp_enqueue_script(
                'custom-admin-script', // Handle
                plugin_dir_url(__FILE__) . '../admin/js/custom-admin.js?'.time(), // Path to JS file
                array('jquery'), // Dependencies
                null, // Version (optional)
                true // Load in footer
            );
            wp_localize_script('custom-admin-script', 'customElementorAjax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('custom_elementor_nonce')
            ]);
        }
    }*/
    
    public function enqueue_admin_scripts($hook) {
        // Get enabled log options from settings
        //$enabled_logs = get_option('enable_notifications', []);
        
        $options = get_option('lhg_activity_plugin_settings');
        $logs = isset($options['enable_to_log']) ? explode(',', $options['enable_to_log']) : [];
        $enabled_logs_type = [];
        $enabled_logs_term = [];
    
        // Get the current admin screen
        $screen = get_current_screen();
        // print_r($screen);
        // die;
        
        // post and page
        if (isset($screen->post_type) && (
            (in_array('page', $logs) && $screen->post_type == 'page') || 
            (in_array('post', $logs) && $screen->post_type == 'post')
        )) {
            $enabled_logs_type[] = 'post';
            $enabled_logs_type[] = 'edit';
        }

        // Plugin
        if (isset($screen->id) && in_array('plugins', $logs) && ($screen->id === 'plugins')) {
            $enabled_logs_type[] = 'plugins';
        }

        // Admin Users  
        if (isset($screen->id) && in_array('users', $logs) && ($screen->id === 'users' || $screen->id === 'user')) {  
            $enabled_logs_type[] = 'users';  
            $enabled_logs_type[] = 'user';  
        }

        // themes
        if (isset($screen->id) && in_array('themes', $logs) && ($screen->id === 'themes' || $screen->id === 'theme-install')) {
            $enabled_logs_type[] = 'themes';
            $enabled_logs_type[] = 'theme-install';
        }       
        

        // Dynamically include custom post types
        // $custom_post_types = get_post_types(['_builtin' => false], 'objects');
        $custom_post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects');
        foreach ($custom_post_types as $post_type => $post_type_obj) {
            if (in_array($post_type, $logs) && $screen->post_type == $post_type) {
                $enabled_logs_type[] = 'post';
                $enabled_logs_type[] = 'edit';
            }
        }

        // Dynamically include custom taxonomy types
        // $custom_taxonomies = get_taxonomies(['_builtin' => false], 'objects');
        $custom_taxonomies = get_taxonomies(array('public' => true, '_builtin' => false), 'objects');
        foreach ($custom_taxonomies as $taxonomy => $taxonomy_obj) {
            if (in_array($taxonomy, $logs) && $screen->taxonomy == $taxonomy) {
                $enabled_logs_term[] = 'edit-tags';
                $enabled_logs_term[] = 'term';
            }
        }




        if (isset($screen->taxonomy) && (
            (in_array('post_tag', $logs) && $screen->taxonomy == 'post_tag') || 
            (in_array('category', $logs) && $screen->taxonomy == 'category')
        )) {
            $enabled_logs_term[] = 'edit-tags';
            $enabled_logs_term[] = 'term';
        }
        // Enqueue the script only if the condition is met
        if ((in_array($screen->base, $enabled_logs_type) && class_exists('\Elementor\Plugin') && \Elementor\Plugin::instance()->editor->is_edit_mode()) || (in_array($screen->base, $enabled_logs_type)) || (in_array($screen->base, $enabled_logs_term))) {
            wp_enqueue_script(
                'custom-admin-script',
                plugin_dir_url(__FILE__) . '../admin/js/custom-admin.js?'.time(),
                array('jquery'),
                null,
                true
            );
    
            wp_localize_script('custom-admin-script', 'customElementorAjax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('custom_elementor_nonce'),
            ]);
            wp_enqueue_style('my-style', get_stylesheet_uri());
        }
    }
     
    // save and list post and page
    public function save_additional_info($post_id) {
        global $wpdb;
        
        // Ensure the function doesn't run on autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

         // Prevent execution on autosave, revisions, or auto-drafts
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
    
        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    
        // Ensure additional_info exists
        if (isset($_POST['additional_info']) && !empty($_POST['additional_info'])) {
            $additional_info = sanitize_text_field($_POST['additional_info']);
            $activity_type = sanitize_text_field($_POST['activity_type']);

            // Get post title and URL
            $post_title = get_the_title($post_id);
            $post_type = get_post_type($post_id);
            $post_url = get_permalink($post_id);

            // Get current user ID and name
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $user_name = $current_user->display_name;

    
            // Define custom table name (use $wpdb->prefix to ensure correct table prefix)
            $table_name = $wpdb->prefix . 'activity_items_data';
    
            // Insert data into the custom table
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'description' => $additional_info,
                    'page_detail' => $post_title.': '.$post_url,
                    'log_page_type' => $post_type,
                    'log_page_id' => $post_id,
                    'status' => 'active',
                    'activity_type' => $activity_type,
                    'created_at' => gmdate('Y-m-d H:i:s')
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s') // Define data types for security
            );
        }
    }  


    public function save_user_creation_info($user_id) {
        global $wpdb;
    
        // Ensure additional_info exists
        if (isset($_POST['additional_info']) && !empty($_POST['additional_info'])) {
            $additional_info = sanitize_text_field($_POST['additional_info']);
            $activity_type = sanitize_text_field($_POST['activity_type']);
    
            // Get user data
            $user_info = get_userdata($user_id);
            $user_name = $user_info->display_name;
            $user_email = $user_info->user_email;
    
            // Get current page details
            $post_title = $user_info->display_name;
            $post_type = 'user';
            $post_url = admin_url('user-edit.php?user_id=' . $user_id);
    
            // Define custom table name
            $table_name = $wpdb->prefix . 'activity_items_data';
    
            // Insert data into the custom table
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'description' => $additional_info . ': '.' (' . $user_email . ')',
                    'page_detail' => $post_title . ': ' . $post_url,
                    'log_page_type' => $post_type,
                    'log_page_id' => $user_id,
                    'status' => 'active',
                    'activity_type' => $activity_type,
                    'created_at' => gmdate('Y-m-d H:i:s')
                ),
                array('%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s') // Define data types for security
            );
        }
    }
    







    // save and list category and tag
    public function save_custom_category_field($term_id) {
        global $wpdb;
    
        if (isset($_POST['additional_info']) && !empty($_POST['additional_info'])) {
            $additional_info = sanitize_text_field($_POST['additional_info']);
            $activity_type = sanitize_text_field($_POST['activity_type']);
    
            // Get term details using the correct term ID
            $term = get_term($term_id);
            if (!$term || is_wp_error($term)) {
                return; // Exit if term is invalid
            }
    
            $term_name = $term->name;
            $term_slug = $term->slug;
            $taxonomy = $term->taxonomy;
    
            // Get current user ID and name
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $user_name = $current_user->display_name;
    
            // Define custom table name (use $wpdb->prefix to ensure correct table prefix)
            $table_name = $wpdb->prefix . 'activity_items_data';
    
            // Insert data into the custom table
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'description' => $additional_info,
                    'page_detail' => $term_name . ': ' . $term_slug,
                    'log_page_type' => $taxonomy,
                    'log_page_id' => $term_id, // Corrected variable
                    'status' => 'active',
                    'activity_type' => $activity_type,
                    'created_at' => gmdate('Y-m-d H:i:s')
                ),
                array('%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s') // Define data types for security
            );
        }
    }
}

// Initialize the controller
new LHG_Activity_Plugin_Admin_Controller();
?>
