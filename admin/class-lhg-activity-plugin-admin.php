<?php
/**
 * The admin-specific functionality of the plugin.
 */
class LHG_ACTIVITY_Plugin_Admin {

    /**
     * The controller instance.
     */
    private $controller;

    /**
     * Initialize the class.
     */
    public function __construct() {
        $this->controller = new LHG_ACTIVITY_Plugin_Item_Controller();
        
        // Load lhg_List_Table if not loaded
        if (!class_exists('LHG_List_Table')) {
            require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }
        
        // Load the list table class
        require_once LHG_ACTIVITY_PLUGIN_PATH . 'admin/class-lhg-activity-plugin-list-table.php';
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style('lhg-activity-plugin-admin', LHG_ACTIVITY_PLUGIN_URL . 'admin/css/lhg-activity-plugin-admin.css', array(), LHG_ACTIVITY_PLUGIN_VERSION, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script('lhg-activity-plugin-admin', LHG_ACTIVITY_PLUGIN_URL . 'admin/js/lhg-activity-plugin-admin.js', array('jquery'), LHG_ACTIVITY_PLUGIN_VERSION, false);
        
        wp_localize_script('lhg-activity-plugin-admin', 'lhg_activity_plugin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lhg_activity_plugin_nonce')
        ));
    }

    /**
     * Add plugin admin menu.
     */
    public function add_plugin_admin_menu() {
        // Main menu
        add_menu_page(
            __('LHG Activity Plugin', 'lhg-activity-plugin'),
            __('LHG Activity Logs', 'lhg-activity-plugin'),
            'manage_options',
            'lhg-activity-plugin',
            array($this, 'display_plugin_admin_page'),
            'dashicons-database',
            26
        );
        
        // Items submenu
        add_submenu_page(
            'lhg-activity-plugin',
            __('Items List', 'lhg-activity-plugin'),
            __('Items', 'lhg-activity-plugin'),
            'manage_options',
            'lhg-activity-plugin',
            array($this, 'display_plugin_admin_page')
        );
        
        // Add new item submenu
        // add_submenu_page(
        //     'lhg-activity-plugin',
        //     __('Add New Item', 'lhg-activity-plugin'),
        //     __('Add New', 'lhg-activity-plugin'),
        //     'manage_options',
        //     'lhg-activity-plugin-add',
        //     array($this, 'display_add_item_page')
        // );
        
        // Settings submenu
        add_submenu_page(
            'lhg-activity-plugin',
            __('Settings', 'lhg-activity-plugin'),
            __('Settings', 'lhg-activity-plugin'),
            'manage_options',
            'lhg-activity-plugin-settings',
            array($this, 'display_plugin_settings_page')
        );
    }

    /**
     * Add settings action link to the plugins page.
     */
    public function add_action_links($links) {
        $settings_link = array(
            '<a href="' . admin_url('admin.php?page=lhg-activity-plugin-settings') . '">' . __('Settings', 'lhg-activity-plugin') . '</a>',
        );
        return array_merge($settings_link, $links);
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
        register_setting(
            'lhg_activity_plugin_settings_group',
            'lhg_activity_plugin_settings',
            array($this, 'sanitize_settings')
        );
        
        add_settings_section(
            'lhg_activity_plugin_general_section',
            __('General Settings', 'lhg-activity-plugin'),
            array($this, 'general_settings_section_callback'),
            'lhg-activity-plugin-settings'
        );
        
        add_settings_field(
            'items_per_page',
            __('Items Per Page', 'lhg-activity-plugin'),
            array($this, 'items_per_page_callback'),
            'lhg-activity-plugin-settings',
            'lhg_activity_plugin_general_section'
        );
        
        add_settings_field(
            'date_format',
            __('Date Format', 'lhg-activity-plugin'),
            array($this, 'date_format_callback'),
            'lhg-activity-plugin-settings',
            'lhg_activity_plugin_general_section'
        );
        
        add_settings_field(
            'enable_notifications',
            __('Enable Notifications', 'lhg-activity-plugin'),
            array($this, 'enable_notifications_callback'),
            'lhg-activity-plugin-settings',
            'lhg_activity_plugin_general_section'
        );

        add_settings_field(
            'enable_to_log',
            __('Enable To Log', 'lhg-activity-plugin'),
            array($this, 'enable_to_log_callback'),
            'lhg-activity-plugin-settings',
            'lhg_activity_plugin_general_section'
        );
    }

    /**
     * Sanitize settings.
     */
    public function sanitize_settings($input) {
        $new_input = array();
        
        if (isset($input['items_per_page'])) {
            $new_input['items_per_page'] = absint($input['items_per_page']);
        }
        
        if (isset($input['date_format'])) {
            $new_input['date_format'] = sanitize_text_field($input['date_format']);
        }
        
        if (isset($input['enable_notifications'])) {
            $new_input['enable_notifications'] = sanitize_text_field($input['enable_notifications']);
        }

        // if (isset($input['enable_to_log'])) {
        //     $new_input['enable_to_log'] = sanitize_text_field($input['enable_to_log']);
        // }

        if (isset($input['enable_to_log']) && is_array($input['enable_to_log'])) {
            // Sanitize each value and convert the array to a comma-separated string
            $new_input['enable_to_log'] = implode(',', array_map('sanitize_text_field', $input['enable_to_log']));
        } else {
            $new_input['enable_to_log'] = ''; // Default to an empty string if no checkboxes are selected
        }
     

        return $new_input;
    }

    /**
     * General settings section callback.
     */
    public function general_settings_section_callback() {
        echo '<p>' . __('Configure the general settings for the plugin.', 'lhg-activity-plugin') . '</p>';
    }

    /**
     * Items per page callback.
     */
    public function items_per_page_callback() {
        $options = get_option('lhg_activity_plugin_settings');
        $value = isset($options['items_per_page']) ? $options['items_per_page'] : 10;
        
        echo '<input type="number" id="items_per_page" name="lhg_activity_plugin_settings[items_per_page]" value="' . esc_attr($value) . '" min="1" max="100" />';
    }

    /**
     * Date format callback.
     */
    public function date_format_callback() {
        $options = get_option('lhg_activity_plugin_settings');
        $value = isset($options['date_format']) ? $options['date_format'] : 'Y-m-d H:i:s';
        
        echo '<input type="text" id="date_format" name="lhg_activity_plugin_settings[date_format]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . __('PHP date format. Default: Y-m-d H:i:s', 'lhg-activity-plugin') . '</p>';
    }

    /**
     * Enable notifications callback.
     */
    public function enable_notifications_callback() {
        $options = get_option('lhg_activity_plugin_settings');
        $value = isset($options['enable_notifications']) ? $options['enable_notifications'] : 'yes';
        
        echo '<select id="enable_notifications" name="lhg_activity_plugin_settings[enable_notifications]">';
        echo '<option value="yes" ' . selected($value, 'yes', false) . '>' . __('Yes', 'lhg-activity-plugin') . '</option>';
        echo '<option value="no" ' . selected($value, 'no', false) . '>' . __('No', 'lhg-activity-plugin') . '</option>';
        echo '</select>';
    }

    /**
     * Enable post callback.
     */
    public function enable_to_log_callback() {
        $options = get_option('lhg_activity_plugin_settings');
        $logs = isset($options['enable_to_log']) ? explode(',', $options['enable_to_log']) : [];

        // Define the list of default log options
        $log_options = [
            'page' => __('Page', 'lhg-activity-plugin'),
            'post' => __('Post', 'lhg-activity-plugin'),
            'category' => __('Category', 'lhg-activity-plugin'),
            'post_tag' => __('Tag', 'lhg-activity-plugin'),
            'themes' => __('Themes', 'lhg-activity-plugin'),
            'plugins' => __('Plugin', 'lhg-activity-plugin'),
            'users' => __('User', 'lhg-activity-plugin'),
            'lhg-activity-logs_page_lhg-activity-plugin-settings' => __('LHG Activity Settings', 'lhg-activity-plugin'),
        ];

        // Get all registered custom post types (excluding default ones)
       // $custom_post_types = get_post_types(['_builtin' => false], 'objects');
        $custom_post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects');
        // Append custom post types to $log_options
        foreach ($custom_post_types as $post_type => $post_type_obj) {
            $log_options[$post_type] = __($post_type_obj->labels->singular_name, 'lhg-activity-plugin');
        }



        // Get all registered custom taxonomies (excluding default ones)
        // $custom_taxonomies = get_taxonomies(['_builtin' => false], 'objects');
        $custom_taxonomies = get_taxonomies(array('public' => true, '_builtin' => false), 'objects');
        // Append custom taxonomies to $log_options
        foreach ($custom_taxonomies as $taxonomy => $taxonomy_obj) {
            $log_options[$taxonomy] = __($taxonomy_obj->labels->singular_name, 'lhg-activity-plugin');
        }




        // Generate checkboxes dynamically
        foreach ($log_options as $key => $label) {
            $checked = (in_array($key, $logs) && !empty($key)) ? 'checked' : '';
            echo "<label><input type='checkbox' name='lhg_activity_plugin_settings[enable_to_log][]' value='$key' $checked> $label</label><br>";
        }
    }
    
    

    /**
     * Display the main admin page.
     */
    public function display_plugin_admin_page() {
        $list_table = new LHG_ACTIVITY_Plugin_List_Table();
        $list_table->prepare_items();
        
        $this->controller->render_view('admin-list', array(
            'list_table' => $list_table
        ));
    }

    /**
     * Display the add/edit item page.
     */
    public function display_add_item_page() {
        $item = array(
            'id' => 0,
            'name' => '',
            'description' => '',
            'status' => 'active'
        );
        
        // Check if we're editing an existing item
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $item_id = intval($_GET['id']);
            $item_data = $this->controller->get_item($item_id);
            
            if ($item_data) {
                $item = $item_data;
            }
        }
        
        $this->controller->render_view('admin-form', array(
            'item' => $item
        ));
    }

    /**
     * Display the settings page.
     */
    public function display_plugin_settings_page() {
        $this->controller->render_view('admin-settings');
    }

    /**
     * AJAX handler for adding an item.
     */
    public function ajax_add_item() {
        check_ajax_referer('lhg_activity_plugin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'lhg-activity-plugin')));
        }
        
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active';
        
        $data = array(
            'name' => $name,
            'description' => $description,
            'status' => $status
        );
        
        $result = $this->controller->create_item($data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } elseif ($result) {
            wp_send_json_success(array(
                'message' => __('Item added successfully.', 'lhg-activity-plugin'),
                'id' => $result
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to add item.', 'lhg-activity-plugin')));
        }
    }

    /**
     * AJAX handler for updating an item.
     */
    public function ajax_update_item() {
        check_ajax_referer('lhg_activity_plugin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'lhg-activity-plugin')));
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active';
        
        $data = array(
            'name' => $name,
            'description' => $description,
            'status' => $status
        );
        
        $result = $this->controller->update_item($id, $data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } elseif ($result) {
            wp_send_json_success(array('message' => __('Item updated successfully.', 'lhg-activity-plugin')));
        } else {
            wp_send_json_error(array('message' => __('Failed to update item.', 'lhg-activity-plugin')));
        }
    }

    /**
     * AJAX handler for deleting an item.
     */
    public function ajax_delete_item() {
        check_ajax_referer('lhg_activity_plugin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'lhg-activity-plugin')));
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        $result = $this->controller->delete_item($id);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Item deleted successfully.', 'lhg-activity-plugin')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete item.', 'lhg-activity-plugin')));
        }
    }

    public function export_to_excel() {
        check_ajax_referer('lhg_activity_plugin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'lhg-activity-plugin')));
            return;
        }
    
        global $wpdb;
        $table_name = $wpdb->prefix . 'activity_items_data';
    
        $user = isset($_POST['filter_user']) ? sanitize_text_field($_POST['filter_user']) : '';
        $page_detail = isset($_POST['filter_page_detail']) ? sanitize_text_field($_POST['filter_page_detail']) : '';
        $page_type = isset($_POST['filter_page']) ? sanitize_text_field($_POST['filter_page']) : '';
        $post_id = isset($_POST['post_id']) ? sanitize_text_field($_POST['post_id']) : '';
    
        $where_sql = "WHERE 1=1";
        if (!empty($user)) {
            $where_sql .= $wpdb->prepare(" AND user_id = %s", $user);
        }
        // if (!empty($user)) {
        //     $where_sql .= " AND CONCAT(u.first_name, ' ', u.last_name) LIKE %s";
        //     $params[] = '%' . $wpdb->esc_like($user) . '%';
        // }
        if (!empty($page_detail)) {
            $where_sql .= $wpdb->prepare(" AND page_detail LIKE %s", '%' . $wpdb->esc_like($page_detail) . '%');
        }
        if (!empty($page_type)) {
            $where_sql .= $wpdb->prepare(" AND log_page_type LIKE %s", '%' . $wpdb->esc_like($page_type) . '%');
        }
        if (!empty($post_id)) {
            $where_sql .= $wpdb->prepare(" AND log_page_id LIKE %s", '%' . $wpdb->esc_like($post_id) . '%');
        }
        $query = "SELECT u.first_name, u.last_name, l.page_detail, l.log_page_type 
          FROM {$wpdb->prefix}log_table l 
          INNER JOIN {$wpdb->prefix}users u ON l.user_id = u.ID 
          $where_sql";
    
        $results = $wpdb->get_results("SELECT * FROM $table_name $where_sql", ARRAY_A);
    
        if (empty($results)) {
            error_log("Error: No data found for export.");
            wp_send_json_error(array('message' => __('No data available to export.', 'lhg-activity-plugin')));
            return;
        }
    
        ob_start();
        echo '<table border="1">';
        echo '<tr>';
        foreach (array_keys($results[0]) as $header) {
            echo "<th>" . esc_html($header) . "</th>";
        }
        echo '</tr>';
    
        foreach ($results as $row) {
            echo '<tr>';
            foreach ($row as $value) {
                echo "<td>" . esc_html($value) . "</td>";
            }
            echo '</tr>';
        }
        echo '</table>';
    
        $table_html = ob_get_clean();        
        wp_send_json_success(array('message' => __('Ajax triggered successfully.', 'lhg-activity-plugin'), 'table' => $table_html));
    }

    public function custom_enqueue_admin_script() {
        // Only load the script on post editor pages
        if (get_current_screen()->base === 'post') {
            wp_enqueue_script(
                'custom-admin-script', // Handle
                get_template_directory_uri() . '/js/custom-admin.js', // Path to your JS file
                array('jquery'), // Dependencies
                null, // Version (optional)
                true // Load in footer
            );
        }
    }


    public function save_elementor_log() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'custom_elementor_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce']);
        }

        global $wpdb;

        // Ensure additional_info exists
        if (isset($_POST['additional_info']) && !empty($_POST['additional_info']) || (isset($_POST['second_additional_info']) && !empty($_POST['second_additional_info']))) {
            $additional_info = sanitize_text_field($_POST['additional_info']);
            $second_additional_info = sanitize_text_field($_POST['second_additional_info']);
            $activity_type = sanitize_text_field($_POST['activity_type']);
            

            $postIds = explode(',', $_POST['post_id']);
            
            for($i=0; $i<count($postIds); $i++){

                //if(str_starts_with($activity_type, 'delete_taxonomy')){
                if (str_starts_with($activity_type, 'delete_taxonomy') || str_starts_with($activity_type, 'bulk_delete_taxonomy')){
                    $term = get_term($postIds[$i]);
                    if (!$term || is_wp_error($term)) {
                        return; 
                    }            
                    $post_title = $term->name;
                    $post_url = $term->slug;
                    $post_type = $term->taxonomy;
                } else if(str_starts_with($activity_type, 'bulk_plugin_active') || str_starts_with($activity_type, 'bulk_plugin_deactivate') || str_starts_with($activity_type, 'bulk_plugin_delete') || str_starts_with($activity_type, 'bulk_plugin_update')  || str_starts_with($activity_type, 'bulk_plugin_enable_auto_update')  || str_starts_with($activity_type, 'bulk_plugin_disable_auto_update')){  
                    $tempDatasplt = explode('/', $postIds[$i]); // Split the value by '/'
                    $post_title = $tempDatasplt[0]; // Extract name (e.g., "health-check")
                    $post_url = $postIds[$i];   // Extract URL (e.g., "health-check.php")
                    $post_type = 'plugin'; // Define a default type for clarity
                } else if($postIds[$i]==0 && isset($_POST['non_post']) && !empty($_POST['non_post'])) {
                    // Get post title and URL
                    $tempData = explode('::', $_POST['non_post']);
                    $post_title = $tempData[2];
                    $post_type = $tempData[0];
                    $post_url = $tempData[3];
                } else if($postIds[$i]>0 && isset($_POST['admin_user']) && !empty($_POST['admin_user'])) {
                    // Get post title and URL
                    $tempDataUser = explode('::', $_POST['admin_user']);
                    $user_id = $tempDataUser[2]; // Assuming the user ID is stored here
                    $user_info = get_userdata($postIds[$i]);
                    if ($user_info) {
                        $post_title = $user_info->display_name; 
                        $post_type = 'user';
                        $post_url = $tempDataUser[3]; 
                        $activity_type = $tempDataUser[1];
                    }
                } else {
                    // Get post title and URL
                    $post_title = get_the_title($postIds[$i]);
                    $post_type = get_post_type($postIds[$i]);
                    $post_url = get_permalink($postIds[$i]);
                }
                
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
                        'status' => $second_additional_info,
                        'page_detail' => $post_title.': '.$post_url,
                        'log_page_type' => $post_type,
                        'log_page_id' => $postIds[$i],
                        'activity_type' => $activity_type,
                        'created_at' => gmdate('Y-m-d H:i:s')
                    ),
                    array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s') // Define data types for security
                );
            }
        }    
        wp_send_json_success(['message' => 'Data saved', 'data' => $extra_data]);
    }
    
}

