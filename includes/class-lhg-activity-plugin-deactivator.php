<?php
/**
 * Fired during plugin deactivation.
 */
class LHG_ACTIVITY_Plugin_Deactivator {

    /**
     * Plugin deactivation tasks.
     */
    public static function deactivate() {
        // Cleanup tasks if needed
        // Note: We're not deleting the table here to prevent data loss
    }
}

