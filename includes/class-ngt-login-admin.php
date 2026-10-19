<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class NGT_Login_Admin {

    public function __construct() {
        // Filters the text alternative for the login header link image
        add_filter( 'login_headertext', array( $this, 'ngt_clean_logo_title' ) );
    }

    /**
     * Replaces standard "Powered by WordPress" logo title text with clean agency portal branding
     */
    public function ngt_clean_logo_title() {
        return get_bloginfo( 'name' ) . ' ' . __( 'Secure Portal', 'ngt-easy-login-customizer' );
    }
}