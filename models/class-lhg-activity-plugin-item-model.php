<?php
/**
 * The model for Activity operations.
 */
class LHG_ACTIVITY_Plugin_Item_Model {
    
    /**
     * The table name.
     */
    private $table_name;
    
    /**
     * Initialize the model.
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'activity_items_data';
    }
    
    /**
     * Get all items with pagination.
     */
    public function get_items($per_page = 10, $page_number = 1, $orderby = 'id', $order = 'DESC') {
        global $wpdb;
        
        $sql = "SELECT * FROM {$this->table_name}";
        
        if (!empty($orderby) && !empty($order)) {
            $sql .= " ORDER BY {$orderby} {$order}";
        }
        
        $sql .= " LIMIT {$per_page}";
        $sql .= " OFFSET " . ($page_number - 1) * $per_page;
        
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Get item by ID.
     */
    public function get_item($id) {
        global $wpdb;
        
        $sql = $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id);
        
        return $wpdb->get_row($sql, ARRAY_A);
    }
    
    /**
     * Create a new item.
     */
    public function create_item($data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'name' => $data['name'],
                'description' => $data['description'],
                'status' => isset($data['status']) ? $data['status'] : 'active'
            )
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Update an existing item.
     */
    public function update_item($id, $data) {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->table_name,
            array(
                'name' => $data['name'],
                'description' => $data['description'],
                'status' => $data['status']
            ),
            array('id' => $id)
        );
        
        return $result !== false;
    }
    
    /**
     * Delete an item.
     */
    public function delete_item($id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $id)
        );
        
        return $result !== false;
    }
    
    /**
     * Get total items count.
     */
    public function record_count() {
        global $wpdb;
        
        $sql = "SELECT COUNT(*) FROM {$this->table_name}";
        
        return $wpdb->get_var($sql);
    }
}

