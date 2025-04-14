<?php
/**
 * Admin form view.
 */
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo $item['id'] ? esc_html__('Edit Item', 'lhg-activity-plugin') : esc_html__('Add New Item', 'lhg-activity-plugin'); ?>
    </h1>
    <hr class="wp-header-end">
    
    <div id="lhg-activity-message" class="notice is-dismissible" style="display:none;"></div>
    
    <form id="lhg-activity-item-form" method="post">
        <input type="hidden" name="id" value="<?php echo esc_attr($item['id']); ?>">
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="name"><?php echo esc_html__('Name', 'lhg-activity-plugin'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="name" id="name" class="regular-text" value="<?php echo esc_attr($item['name']); ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="description"><?php echo esc_html__('Description', 'lhg-activity-plugin'); ?></label>
                    </th>
                    <td>
                        <textarea name="description" id="description" class="large-text" rows="5"><?php echo esc_textarea($item['description']); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="status"><?php echo esc_html__('Status', 'lhg-activity-plugin'); ?></label>
                    </th>
                    <td>
                        <select name="status" id="status">
                            <option value="active" <?php selected($item['status'], 'active'); ?>><?php echo esc_html__('Active', 'lhg-activity-plugin'); ?></option>
                            <option value="inactive" <?php selected($item['status'], 'inactive'); ?>><?php echo esc_html__('Inactive', 'lhg-activity-plugin'); ?></option>
                            <option value="pending" <?php selected($item['status'], 'pending'); ?>><?php echo esc_html__('Pending', 'lhg-activity-plugin'); ?></option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary" id="lhg-activity-save-item">
                <?php echo $item['id'] ? esc_html__('Update Item', 'lhg-activity-plugin') : esc_html__('Add Item', 'lhg-activity-plugin'); ?>
            </button>
        </p>
    </form>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#lhg-activity-item-form').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            var isEdit = $('input[name="id"]').val() > 0;
            
            $.ajax({
                url: lhg_activity_plugin.ajax_url,
                type: 'POST',
                data: {
                    action: isEdit ? 'lhg_activity_update_item' : 'lhg_activity_add_item',
                    nonce: lhg_activity_plugin.nonce,
                    ...Object.fromEntries(new FormData(this))
                },
                success: function(response) {
                    if (response.success) {
                        $('#lhg-activity-message').removeClass('notice-error').addClass('notice-success').html('<p>' + response.data.message + '</p>').show();
                        
                        if (!isEdit) {
                            // Redirect to edit page if it's a new item
                            setTimeout(function() {
                                window.location.href = '<?php echo esc_url(admin_url('admin.php?page=lhg-activity-plugin-add&action=edit')); ?>&id=' + response.data.id;
                            }, 1000);
                        }
                    } else {
                        $('#lhg-activity-message').removeClass('notice-success').addClass('notice-error').html('<p>' + response.data.message + '</p>').show();
                    }
                }
            });
        });
    });
</script>

