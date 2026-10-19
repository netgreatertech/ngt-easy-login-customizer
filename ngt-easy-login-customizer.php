<?php
/**
 * Plugin Name:       NGT Easy Login Customizer
 * Plugin URI:        https://netgreater.com/updates/plugins/ngt-easy-login-customizer-1.1.0.zip
 * Description:       A modern, ultra-lightweight security endpoint router and login screen decorator tailored for client handoffs.
 * Version:           1.1.0
 * Author:            Net Greater Technologies, LLP
 * Author URI:        https://netgreater.com/
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL2
 * Text Domain:       ngt-easy-login-customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Global Core Framework Constraints
define( 'NGT_CUSTOMIZER_VERSION', '1.0.0' );
define( 'NGT_CUSTOMIZER_DIR', plugin_dir_path( __FILE__ ) );
define( 'NGT_CUSTOMIZER_URL', plugin_dir_url( __FILE__ ) );

/**
 * Core Orchestration Class for Net Greater Technologies
 */
final class NGT_Login_Engine {
    
    private static $instance = null;

    /**
     * Singleton instance provider
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor: Loads components and locks down configuration dependencies
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_components();
    }

    /**
     * Safely imports all 13 underlying modular files
     */
    private function load_dependencies() {
        $modules = array(
            'security',   // Handles brute-force obfuscation error overrides
            'access',     // Handles frontend dashboard restriction logic
            'admin',      // Modernizes workspace elements for clean handoffs
            'appearance', // Controls micro-animations and layout fade-ups
            'customiser', // Connects configuration values with native WP APIs
            'log',        // Tracks security telemetry error notifications
            'network',    // Manages multi-tenant enterprise network hooks
            'options',    // Handles variable field token normalization
            '2fa',        // Extensible template for multi-factor protocols
            'qr',         // Extensible layer mapping visual device tokens
            'redirect',   // Directs user destinations dynamically by role
            'updater',    // Remote API check connector pointing to your json file
            'url'         // Manages dynamic entry keys and 404 filtering locks
        );

        foreach ( $modules as $module ) {
            $file = NGT_CUSTOMIZER_DIR . "includes/class-ngt-login-{$module}.php";
            if ( file_exists( $file ) ) {
                require_once $file;
            }
        }
    }

    /**
     * Instantiates structural engine classes during execution loops
     */
    private function init_components() {
        new NGT_Login_Security(); 
        new NGT_Login_Customiser(); 
        new NGT_Login_URL(); 
        new NGT_Login_Access(); 
        new NGT_Login_Admin(); 
        new NGT_Login_Appearance(); 
        new NGT_Login_Log(); 
        new NGT_Login_Network(); 
        new NGT_Login_Options(); 
        new NGT_Login_2FA(); 
        new NGT_Login_QR(); 
        new NGT_Login_Redirect(); 
        new NGT_Login_Updater();  
    }
}

// Instantiate the master plugin infrastructure safely once core arrays load
add_action( 'plugins_loaded', array( 'NGT_Login_Engine', 'get_instance' ) );