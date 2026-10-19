<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class NGT_Login_Appearance {
    
    public function __construct() {
        // Enqueues the custom smooth transitions directly onto the login screen
        add_action( 'login_enqueue_scripts', array( $this, 'inject_smooth_transitions' ), 99 );
    }

    /**
     * Injects custom CSS keyframes to gently animate the login form card on page load
     */
    public function inject_smooth_transitions() {
        ?>
        <style type="text/css">
            .login form {
                animation: ngtFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            }
            @keyframes ngtFadeUp {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
        <?php
    }
}