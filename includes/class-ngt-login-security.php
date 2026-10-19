<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class NGT_Login_Security {
    
    public function __construct() {
        // Intercepts and overrides default login error messages
        add_filter( 'login_errors', array( $this, 'obfuscate_system_errors' ) );
    }

    /**
     * Prevents username harvesting by showing a generic message instead of specific hints.
     */
    public function obfuscate_system_errors() {
        return __( 'Security Check: Access denied due to invalid credentials.', 'ngt-easy-login-customizer' );
    }
}