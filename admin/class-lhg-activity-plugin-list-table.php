<?php
/**
 * List table class for displaying items.
 */
class LHG_ACTIVITY_Plugin_List_Table extends WP_List_Table {
    
    /**
     * The controller instance.
     */
    private $controller;
    
    /**
     * Initialize the class.
     */
    public function __construct() {
        parent::__construct(array(
            'singular' => 'item',
            'plural'   => 'items',
            'ajax'     => false
        ));
        
        $this->controller = new LHG_ACTIVITY_Plugin_Item_Controller();
    }
    
    
    /**
     * Get columns.
     */
    public function get_columns() {
        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'user_id'        => __('User Details', 'lhg-activity-plugin'),
            'description' => __('Description', 'lhg-activity-plugin'),
            'log_page_type' => __('Type', 'lhg-activity-plugin'),
            'page_detail' => __('Page Details', 'lhg-activity-plugin'),             
            'log_page_id' => __('Post ID', 'lhg-activity-plugin'),
            'activity_type' => __('Activity Type', 'lhg-activity-plugin'),
            'status'      => __('Status', 'lhg-activity-plugin'),
            'created_at'  => __('Created', 'lhg-activity-plugin')
        );
        
        return $columns;
    }
    
    /**
     * Get sortable columns.
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'name'       => array('name', true),
            'status'     => array('status', false),
            'created_at' => array('created_at', false)
        );
        
        return $sortable_columns;
    }
    
    /**
     * Column default.
     */

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'user_id':
                return sprintf(
                    '<a href="#" class="user-details-link" data-user-id="%s">%s</a>',
                    esc_attr($item[$column_name]),
                    esc_html($item[$column_name])
                );
            case 'description':
                return wp_trim_words($item[$column_name], 10, '...');
            case 'created_at':
                $options = get_option('lhg_activity_plugin_settings');
                $date_format = isset($options['date_format']) ? $options['date_format'] : 'Y-m-d H:i:s';
                return date($date_format, strtotime($item[$column_name]));
            case 'status':
                return ucfirst($item[$column_name]);
            default:
                return $item[$column_name];
        }
    }
    

    // user name get 
    public function column_user_id($item) {
        $user = get_userdata($item['user_id']); // Fetch user data by ID
        
        if ($user) {
            $first_name = get_user_meta($user->ID, 'first_name', true);
            $last_name  = get_user_meta($user->ID, 'last_name', true);
            $full_name  = trim($first_name . ' ' . $last_name);
    
            return !empty($full_name) ? esc_html($full_name) : esc_html($user->user_login); // Show full name if available, otherwise username
        } else {
            return __('Unknown User', 'lhg-activity-plugin'); // Fallback if user not found
        }
    }
    
    
    /**
     * Column name.
     */
    public function column_name($item) {
        $actions = array(
            'edit'   => sprintf('<a href="?page=%s&action=%s&id=%s">' . __('Edit', 'lhg-activity-plugin') . '</a>', 'lhg-activity-plugin-add', 'edit', $item['id']),
            'delete' => sprintf('<a href="#" class="delete-item" data-id="%s">' . __('Delete', 'lhg-activity-plugin') . '</a>', $item['id'])
        );
        
        return sprintf('%1$s %2$s', $item['name'], $this->row_actions($actions));
    }
    
    /**
     * Column checkbox.
     */
    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="item[]" value="%s" />', $item['id']
        );
    }
    
    /**
     * Get bulk actions.
     */
    public function get_bulk_actions() {
        $actions = array(
            'delete' => __('Delete', 'lhg-activity-plugin')
        );
        
        return $actions;
    }
    
    /**
     * Process bulk actions.
     */
    public function process_bulk_action() {
        if ('delete' === $this->current_action()) {
            if (isset($_POST['item']) && is_array($_POST['item'])) {
                foreach ($_POST['item'] as $id) {
                    $this->controller->delete_item(absint($id));
                }
            }
        }
    }
    
    /**
     * Prepare items.
     */
    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'activity_items_data'; // Replace with your table name

        
    
        $query = "SELECT * FROM $table_name";
        $this->items = $wpdb->get_results($query, ARRAY_A);
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
    
        $this->_column_headers = array($columns, $hidden, $sortable);


        $this->process_bulk_action();
        
        $per_page = $this->get_items_per_page('items_per_page', 10);
        $current_page = $this->get_pagenum();
        
        $orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
        $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'DESC';
    
        $filter_user = isset($_REQUEST['filter_user']) ? sanitize_text_field($_REQUEST['filter_user']) : '';
        $filter_page_detail = isset($_REQUEST['filter_page_detail']) ? sanitize_text_field($_REQUEST['filter_page_detail']) : '';
        $filter_page_type = isset($_REQUEST['filter_page']) ? sanitize_text_field($_REQUEST['filter_page']) : '';
        $post_id = isset($_REQUEST['post_id']) ? intval($_REQUEST['post_id']) : 0;
    
        $this->_column_headers = array(
            $this->get_columns(),
            array(),
            $this->get_sortable_columns()
        );
    
        // Fetch total count after applying filters
        $total_items = $this->controller->get_filtered_items_count($filter_user, $filter_page_detail, $filter_page_type, $post_id);
    
        // Fetch the filtered data
        $this->items = $this->controller->get_filtered_items($per_page, $current_page, $orderby, $order, $filter_user, $filter_page_detail, $filter_page_type, $post_id);
    
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }
    // search and export function
    function extra_tablenav($which) {
        if ($which === 'top') {
            $fltr_str = '';

            $selected_user = isset($_GET['filter_user']) ? sanitize_text_field($_GET['filter_user']) : '';
            $selected_page_detail = isset($_GET['filter_page_detail']) ? sanitize_text_field($_GET['filter_page_detail']) : '';
            $selected_page_type = isset($_GET['filter_page']) ? sanitize_text_field($_GET['filter_page']) : '';
            $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
            if($selected_user!=''){
                $fltr_str .= '&filter_user='.$_GET['filter_user'];
            }
            if($selected_page_detail!=''){
                $fltr_str .= '&filter_page_detail='.$_GET['filter_page_detail'];
            }
            if($selected_page_type!=''){
                $fltr_str .= '&filter_page='.$_GET['filter_page'];
            }
    
            ?>
                <div class="alignleft actions">
                    <!-- User Filter Dropdown -->
                    <select name="filter_user" id="filter_user">
                        <option value=""><?php _e('Filter by User', 'lhg-activity-plugin'); ?></option>
                        <?php
                        global $wpdb;
                        $users = $wpdb->get_results("SELECT DISTINCT user_id FROM {$wpdb->prefix}activity_items_data");
                        
                        foreach ($users as $user) {
                            $user_data = get_userdata($user->user_id); // Fetch user data
                            if ($user_data) {
                                $first_name = get_user_meta($user_data->ID, 'first_name', true);
                                $last_name  = get_user_meta($user_data->ID, 'last_name', true);
                                $full_name  = trim($first_name . ' ' . $last_name);
                                $display_name = !empty($full_name) ? $full_name : $user_data->user_login; // Show full name if available, otherwise username

                                echo '<option value="' . esc_attr($user->user_id) . '" ' . selected($selected_user, $user->user_id, false) . '>' . esc_html($display_name) . '</option>';
                            }
                        }
                        ?>
                    </select>

        
                    <!-- Page Type Filter Dropdown -->
                    <select name="filter_page" id="filter_page">
                        <option value=""><?php _e('Filter by Type', 'lhg-activity-plugin'); ?></option>
                        <option value="page" <?php selected($selected_page_type, 'page'); ?>><?php _e('Page', 'lhg-activity-plugin'); ?></option>
                        <option value="post" <?php selected($selected_page_type, 'post'); ?>><?php _e('Post', 'lhg-activity-plugin'); ?></option>
                        <option value="tag" <?php selected($selected_page_type, 'tag'); ?>><?php _e('Tag', 'lhg-activity-plugin'); ?></option>
                        <option value="category" <?php selected($selected_page_type, 'category'); ?>><?php _e('Category', 'lhg-activity-plugin'); ?></option>
                        <option value="theme" <?php selected($selected_page_type, 'theme'); ?>><?php _e('Theme', 'lhg-activity-plugin'); ?></option>
                        <option value="plugin" <?php selected($selected_page_type, 'plugin'); ?>><?php _e('Plugin', 'lhg-activity-plugin'); ?></option>
                    </select>
        
                    <!-- Description Filter (Text Input) -->
                    <input type="text" id="filter_page_detail" name="filter_page_detail" placeholder="<?php _e('Filter by Key Word', 'lhg-activity-plugin'); ?>" value="<?php echo esc_attr($selected_page_detail); ?>">
        
                    <input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?php _e('Filter', 'lhg-activity-plugin'); ?>">
                    <input type="hidden" name="post_id" id="post_id" value="<?php echo isset($_GET['post_id']) ? esc_attr($_GET['post_id']) : ''; ?>">
                    <input type="button" id="clear-filters" class="button" value="<?php _e('Clear Filters', 'lhg-activity-plugin'); ?>">
                    
                    <a href="#" class="button button-primary export-excel-btn"><?php _e('Export', 'lhg-activity-plugin'); ?></a>
                    
                </div>

                <!-- AJAX Script to Handle Filtering Without Reload -->
                <script type="text/javascript">
                    document.addEventListener("DOMContentLoaded", function () {
                        let userFilter = document.getElementById("filter_user");
                        let pageFilter = document.getElementById("filter_page");
                        let descriptionFilter = document.getElementById("filter_page_detail");
                        let postIdLog = document.getElementById("post_id");
                        let filterButton = document.getElementById("post-query-submit");
                        let clearFilterButton = document.getElementById("clear-filters");

                        function applyFilters() {
                            let params = new URLSearchParams(window.location.search);
                            params.set("filter_user", userFilter.value);
                            params.set("filter_page", pageFilter.value);
                            params.set("filter_page_detail", descriptionFilter.value);
                            params.set("post_id", postIdLog.value);

                            window.location.href = 'admin.php?'+params;
                        }

                        // Event listeners for filtering
                        userFilter.addEventListener("change", applyFilters);
                        pageFilter.addEventListener("change", applyFilters);
                        descriptionFilter.addEventListener("input", function () {
                            clearTimeout(this.timer);
                            this.timer = setTimeout(applyFilters, 500); // Delay for better performance
                        });

                        // ✅ Clear all filters when the "Clear Filters" button is clicked
                        clearFilterButton.addEventListener("click", function () {
                            //let url = new URL(window.location.href);
                            userFilter.value = "";
                            pageFilter.value = "";
                            descriptionFilter.value = "";
                            applyFilters();
                        });
                    });
                </script>

                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        document.querySelector(".export-excel-btn").addEventListener("click", function (event) {
                            event.preventDefault(); // Prevent default link action
                            fetchDataAndExport();
                        });
                    });

                    function fetchDataAndExport() {
                        var formData = new FormData();
                        formData.append('action', 'lhg_activity_export_to_excel');
                        // Collect filter values if available
                        var user = document.querySelector("#filter_user") ? document.querySelector("#filter_user").value : "";
                        var description = document.querySelector("#filter_page_detail") ? document.querySelector("#filter_page_detail").value : "";
                        var pageType = document.querySelector("#filter_page") ? document.querySelector("#filter_page").value : "";
                        var postId = document.querySelector("#post_id") ? document.querySelector("#post_id").value : "";


                        formData.append('filter_user', user);
                        formData.append('filter_page_detail', description);
                        formData.append('filter_page', pageType);
                        formData.append('post_id', postId); // Ensure post ID is passed

                        jQuery.ajax({
                            url: lhg_activity_plugin.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'lhg_activity_export_to_excel',
                                nonce: lhg_activity_plugin.nonce,
                                ...Object.fromEntries(formData)
                            },
                            success: function(response) {
                                if (response.success) {
                                    jQuery('#lhg-activity-message').removeClass('notice-error').addClass('notice-success').html('<p>' + response.data.message + '</p>').show(); 
                                    console.log('Response table',response.data.table);  
                                    createExcelFile(response.data.table);                                 
                                } else {
                                    jQuery('#lhg-activity-message').removeClass('notice-success').addClass('notice-error').html('<p>' + response.data.message + '</p>').show();
                                }
                            }
                        });  
                    }
                    function createExcelFile(tableHTML) {
                        var fullHTML = "<html xmlns:x=\"urn:schemas-microsoft-com:office:excel\">";
                        fullHTML += "<head><meta charset='UTF-8'></head><body>";
                        fullHTML += tableHTML + "</body></html>";

                        var fileName = "export_data.xls";
                        var blob = new Blob([fullHTML], { type: 'application/vnd.ms-excel' });
                        var a = document.createElement('a');
                        var url = URL.createObjectURL(blob);
                        a.href = url;
                        a.download = fileName;

                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                    }
                </script>
            <?php
        }
    } 
        
}

