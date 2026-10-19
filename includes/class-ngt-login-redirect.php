<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class NGT_Login_Redirect {

    public function __construct() {
        // Intercepts the successful landing destination routing logic
        add_filter( 'login_redirect', array( $this, 'route_user_by_role' ), 10, 3 );
    }

    /**
     * Dynamically routes authorized profiles based on user capabilities
     */
    public function route_user_by_role( $redirect_to, $request, $user ) {
        // If the user object is an authentication error, let WordPress handle it natively
        if ( ! is_a( $user, 'WP_User' ) ) {
            return $redirect_to;
        }

        if ( isset( $user->roles ) && is_array( $user->roles ) ) {
            // Send site administrators directly into the core dashboard panels
            if ( in_array( 'administrator', $user->roles ) ) {
                return admin_url();
            }
        }

        // Send all alternative user roles (Subscribers, Customers, etc.) to the frontend homepage
        return home_url();
    }
}