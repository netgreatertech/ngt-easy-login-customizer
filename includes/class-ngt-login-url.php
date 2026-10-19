<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class NGT_Login_Url {

    public function __construct() {
        // 1. Intercept the custom slug request and load the login screen natively
        add_action( 'init', array( $this, 'intercept_custom_login_route' ) );

        // 2. Block default wp-login.php access and enforce the Secret Key
        add_action( 'login_init', array( $this, 'enforce_security_gateway' ) );

        // 3. Automatically rewrite all WordPress-generated login URLs to your new slug and key
        add_filter( 'login_url', array( $this, 'filter_login_url' ), 10, 3 );
        add_filter( 'site_url', array( $this, 'filter_site_url' ), 10, 4 );
        add_filter( 'network_site_url', array( $this, 'filter_site_url' ), 10, 3 );
    }

    /**
     * Listens for the custom slug in the URL and silently loads wp-login.php
     */
    public function intercept_custom_login_route() {
        $slug = get_option('ngt_custom_login_slug', '');
        if ( empty( $slug ) ) return;

        // Parse the requested path and clean up slashes
        $req_uri = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
        $home_path = trim( parse_url( home_url(), PHP_URL_PATH ), '/' );
        
        if ( ! empty( $home_path ) && strpos( $req_uri, $home_path ) === 0 ) {
            $req_uri = trim( substr( $req_uri, strlen( $home_path ) ), '/' );
        }

        // If the URL matches our custom slug, load the login screen
        if ( $req_uri === $slug ) {
            global $error, $action, $user_login, $interim_login;
            $_SERVER['SCRIPT_NAME'] = '/wp-login.php';
            require_once ABSPATH . 'wp-login.php';
            exit;
        }
    }

    /**
     * Secures the login page: Blocks standard wp-login.php and requires the dynamic key
     */
    public function enforce_security_gateway() {
        $slug = get_option( 'ngt_custom_login_slug', '' );
        $key  = get_option( 'ngt_gateway_secret', '' );
        $is_logout = isset( $_GET['action'] ) && $_GET['action'] === 'logout';

        // Always allow logout to proceed normally
        if ( $is_logout ) return;

        // Block access if they are trying to reach standard wp-login.php directly
        if ( ! empty( $slug ) ) {
            $requested_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
            if ( strpos( $requested_path, 'wp-login.php' ) !== false ) {
                // Safely bounce them back to the frontend to a fake URL to trigger a natural 404
                wp_safe_redirect( home_url( '/404-error-page' ) ); 
                exit;
            }
        }

        // Block access if the dynamic security key is missing from the URL
        if ( ! empty( $key ) ) {
            $query_string = isset( $_SERVER['QUERY_STRING'] ) ? $_SERVER['QUERY_STRING'] : '';
            if ( strpos( $query_string, $key ) === false ) {
                // Safely bounce them back to the frontend to a fake URL to trigger a natural 404
                wp_safe_redirect( home_url( '/404-error-page' ) ); 
                exit;
            }
        }
    }

    /**
     * Filters standard login URLs to append the custom slug and security key
     */
    public function filter_login_url( $login_url, $redirect, $force_reauth ) {
        return $this->apply_custom_url( $login_url );
    }

    /**
     * Filters site URLs to catch dynamically generated login links
     */
    public function filter_site_url( $url, $path, $scheme, $blog_id = null ) {
        if ( $path === 'wp-login.php' || preg_match( '/wp-login\.php\?action=\w+/', $path ) ) {
            return $this->apply_custom_url( $url );
        }
        return $url;
    }

    /**
     * Helper function to string-replace default URLs with our secure endpoints
     */
    private function apply_custom_url( $url ) {
        $slug = get_option( 'ngt_custom_login_slug', '' );
        $key  = get_option( 'ngt_gateway_secret', '' );

        if ( ! empty( $slug ) ) {
            $url = str_replace( 'wp-login.php', $slug, $url );
        }
        if ( ! empty( $key ) ) {
            $url = add_query_arg( $key, '', $url );
            $url = str_replace( '=' . '', '', $url ); // Clean up trailing equals sign
        }
        return $url;
    }
}