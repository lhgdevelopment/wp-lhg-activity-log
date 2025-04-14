<?php
/**
 * Fired during plugin activation.
 */
class LHG_ACTIVITY_Plugin_Activator {

    /**
     * Create the database table during plugin activation.
     */
    public static function activate() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'activity_items_data';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            description text NOT NULL,
            log_page_type text NOT NULL,
            log_page_id mediumint(9) NOT NULL,
            page_detail text NOT NULL,
            activity_type text NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

         // Alter the table to add a new column
         /*$column_name = 'category';
         $existing_column = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE '$column_name'");
 
         if (empty($existing_column)) {
             $wpdb->query("ALTER TABLE $table_name ADD COLUMN category VARCHAR(100) NOT NULL DEFAULT 'general';");
         }*/
        
        // Add default options
        add_option('lhg_activity_plugin_settings', array(
            'items_per_page' => 10,
            'date_format' => 'Y-m-d H:i:s',
            'enable_notifications' => 'yes',
            'enable_to_log' => ''
        ));
    }
}

