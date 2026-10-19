<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class NGT_Login_Network {
    public function __construct() {
        add_action( 'wpmu_new_blog', array( $this, 'sync_network_login_defaults' ), 10, 6 );
    }

    /**
     * Automatically provisions brand design guidelines when a new network site is created.
     */
    public function sync_network_login_defaults( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        switch_to_blog( $blog_id );
        // Seed matching corporate identity presets out-of-the-box
        set_theme_mod( 'ngt_base_backdrop', '#f0f4f8' );
        set_theme_mod( 'ngt_brand_interactive', '#0052cc' );
        restore_current_blog();
    }
}