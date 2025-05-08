<?php
/**
 * Admin list view.
 */
?>
<div class="wrap">
    
    <div id="lhg-activity-message" class="notice is-dismissible" style="display:none;"></div>
    
    <form id="lhg-activity-items-list" method="post">
        <?php
        $list_table->display();
        ?>
    </form>
</div>

<script type="text/javascript">
    /*jQuery(document).ready(function($) {
        $('.delete-item').on('click', function(e) {
            e.preventDefault();
            
            if (confirm('<?php echo esc_js(__('Are you sure you want to delete this item?', 'lhg-activity-plugin')); ?>')) {
                var id = $(this).data('id');
                
                $.ajax({
                    url: lhg_activity_plugin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'lhg_activity_delete_item',
                        id: id,
                        nonce: lhg_activity_plugin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#lhg-activity-message').removeClass('notice-error').addClass('notice-success').html('<p>' + response.data.message + '</p>').show();
                            location.reload();
                        } else {
                            $('#lhg-activity-message').removeClass('notice-success').addClass('notice-error').html('<p>' + response.data.message + '</p>').show();
                        }
                    }
                });
            }
        });
    });*/
</script>

