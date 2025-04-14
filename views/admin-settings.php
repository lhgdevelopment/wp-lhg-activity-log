<?php
/**
 * Admin settings view.
 */
?>
<div class="wrap">
    <h1><?php echo esc_html__('LHG Activity Logs Settings', 'lhg-activity-plugin'); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('lhg_activity_plugin_settings_group');
        do_settings_sections('lhg-activity-plugin-settings');
        submit_button();
        ?>
    </form>
</div>

