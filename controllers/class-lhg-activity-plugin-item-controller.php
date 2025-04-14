<?php
/**
 * The controller for Activity operations.
 */
class LHG_ACTIVITY_Plugin_Item_Controller {
    
    /**
     * The model instance.
     */
    private $model;
    
    /**
     * Initialize the controller.
     */
    public function __construct() {
        $this->model = new LHG_ACTIVITY_Plugin_Item_Model();
    }
    
    /**
     * Get all items.
     */
    public function get_items($per_page, $page_number, $orderby = 'id', $order = 'DESC') {
        return $this->model->get_items($per_page, $page_number, $orderby, $order);
    }
    
    /**
     * Get a single item.
     */
    public function get_item($id) {
        return $this->model->get_item($id);
    }
    
    /**
     * Create a new item.
     */
    public function create_item($data) {
        // Validate data
        if (empty($data['name'])) {
            return new WP_Error('invalid_data', __('Name is required', 'lhg-activity-plugin'));
        }
        
        return $this->model->create_item($data);
    }
    
    /**
     * Update an existing item.
     */
    public function update_item($id, $data) {
        // Validate data
        if (empty($data['name'])) {
            return new WP_Error('invalid_data', __('Name is required', 'lhg-activity-plugin'));
        }
        
        return $this->model->update_item($id, $data);
    }
    
    /**
     * Delete an item.
     */
    public function delete_item($id) {
        return $this->model->delete_item($id);
    }
    
    /**
     * Get total items count.
     */
    public function get_items_count() {
        return $this->model->record_count();
    }
    
    /**
     * Render a view.
     */
    public function render_view($view_name, $data = array()) {
        $view_file = LHG_ACTIVITY_PLUGIN_PATH . 'views/' . $view_name . '.php';
        
        if (file_exists($view_file)) {
            extract($data);
            include $view_file;
        } else {
            wp_die(__('View file not found', 'lhg-activity-plugin'));
        }
    }


    public function get_filtered_items_count($user, $filter_page_detail, $page_type, $post_id=0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'activity_items_data';
        
        $where = "WHERE 1=1";
        if (!empty($user)) {
            $where .= $wpdb->prepare(" AND user_id = %s", $user);
        }
        if (!empty($filter_page_detail)) {
            $where .= $wpdb->prepare(" AND page_detail LIKE %s", '%' . $wpdb->esc_like($filter_page_detail) . '%');
        }
        if (!empty($page_type)) {
            $where .= $wpdb->prepare(" AND log_page_type LIKE %s", '%' . $wpdb->esc_like($page_type) . '%');
        }
        if (!empty($post_id > 0)) {
            $where .= $wpdb->prepare(" AND log_page_id = %d", $post_id);
        }
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where");
    }
    
    public function get_filtered_items($per_page, $current_page, $orderby, $order, $user, $filter_page_detail, $page_type, $post_id=0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'activity_items_data';
        
        $offset = ($current_page - 1) * $per_page;
        
        $where = "WHERE 1=1";
        if (!empty($user)) {
            $where .= $wpdb->prepare(" AND user_id = %s", $user);
        }
        if (!empty($filter_page_detail)) {
            $where .= $wpdb->prepare(" AND page_detail LIKE %s", '%' . $wpdb->esc_like($filter_page_detail) . '%');
        }
        if (!empty($page_type)) {
            $where .= $wpdb->prepare(" AND log_page_type LIKE %s", '%' . $wpdb->esc_like($page_type) . '%');
        }
        if (!empty($post_id > 0)) {
            $where .= $wpdb->prepare(" AND log_page_id = %d", $post_id);
        }
        
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name $where ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $offset),
            ARRAY_A
        );
    }
    

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized_input = array();

        if (isset($input['items_per_page'])) {
            $sanitized_input['items_per_page'] = absint($input['items_per_page']);
        }

        if (isset($input['export_format'])) {
            $sanitized_input['export_format'] = sanitize_text_field($input['export_format']);
        }

        return $sanitized_input;
    }

    /**
     * Get plugin settings
     */
    public static function get_settings() {
        $defaults = array(
            'items_per_page' => 10,
            'export_format'  => 'csv',
        );

        $options = get_option(self::OPTION_NAME, array());
        return wp_parse_args($options, $defaults);
    }
      
    
}

