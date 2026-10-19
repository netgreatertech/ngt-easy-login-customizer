<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class NGT_Login_Access {

    public function __construct() {
        // Runs early to catch unauthenticated /wp-admin access before WordPress redirects it
        add_action( 'init', array( $this, 'block_logged_out_admin_access' ) );
        
        // Runs later to check permissions for users who are actually logged in
        add_action( 'admin_init', array( $this, 'restrict_admin_dashboard' ) );
    }

    /**
     * Intercepts unauthenticated users trying to hit /wp-admin and forces a 404
     * instead of letting WordPress automatically redirect them to the secret portal.
     */
    public function block_logged_out_admin_access() {
        // Fix: Only block /wp-admin if the security features are actually turned ON!
        $slug = get_option( 'ngt_custom_login_slug', '' );
        $key  = get_option( 'ngt_gateway_secret', '' );

        // If both the slug and key are empty, the user wants default WordPress behavior.
        if ( empty( $slug ) && empty( $key ) ) {
            return; 
        }

        $requested_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

        // We must allow frontend AJAX and Post requests to pass through (used by plugins/themes)
        if ( strpos( $requested_path, 'admin-ajax.php' ) !== false || strpos( $requested_path, 'admin-post.php' ) !== false ) {
            return;
        }

        // If the URL contains wp-admin and the user is NOT logged in
        if ( strpos( $requested_path, '/wp-admin' ) !== false && ! is_user_logged_in() ) {
            // Bounce them to a safe 404 path
            wp_safe_redirect( home_url( '/404-error-page' ) );
            exit;
        }
    }

    /**
     * Redirects non-administrators trying to access backend paths directly.
     */
    public function restrict_admin_dashboard() {
        // Allow background processing requests to pass through untouched
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        // Rule 1: If they are logged in, but are NOT an administrator, bounce them to the homepage
        if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {
            wp_safe_redirect( home_url() );
            exit;
        }
    }
}