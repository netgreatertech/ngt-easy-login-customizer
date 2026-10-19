<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class NGT_Login_Log {
    
    public function __construct() {
        // Hooks into the core login failure vector to catch unauthorized access attempts
        add_action( 'wp_login_failed', array( $this, 'log_failed_attempts' ) );
    }

    /**
     * Catches and routes failed authentication data directly to the secure server logs
     */
    public function log_failed_attempts( $username ) {
        error_log( sprintf( "NGT Security Notice: Failed login attempt recorded for username: %s", sanitize_text_field( $username ) ) );
    }
}