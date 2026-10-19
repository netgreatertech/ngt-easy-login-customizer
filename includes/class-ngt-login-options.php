<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class NGT_Login_Options {
    public function __construct() {
        add_filter( 'pre_set_theme_mod_ngt_gateway_secret', array( $this, 'sanitize_gateway_input' ) );
    }

    /**
     * Forces strict alphanumeric constraints on security parameters to prevent payload injection attempts.
     */
    public function sanitize_gateway_input( $value ) {
        return preg_replace( '/[^a-zA-Z0-9_-]/', '', $value );
    }
}