<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class NGT_Login_QR {
    private $api_secret_key;

    public function __construct() {
        $this->api_secret_key = get_option('ngt_qr_master_api_key');
        if ( empty($this->api_secret_key) ) {
            $this->api_secret_key = wp_generate_password( 64, true, true );
            update_option('ngt_qr_master_api_key', $this->api_secret_key);
        }

        if ( (int) get_option('ngt_enable_qr_engine', 0) === 1 ) {
            // HIDDEN FOR NOW: Frontend QR buttons and asset injection actions disabled
            // add_action( 'login_form', array( $this, 'inject_qr_login_toggle_button' ) );
            // add_action( 'login_head', array( $this, 'output_qr_layout_constraints' ) );
            
            add_action( 'rest_api_init', array( $this, 'register_qr_endpoint_routes' ) );
            add_action( 'wp_ajax_nopriv_ngt_check_qr_session', array( $this, 'ajax_verify_token_status_fallback' ) );
        }
    }

    public function output_qr_layout_constraints() {
        ?>
        <style type="text/css">
            .ngt-qr-container img { max-width: 100% !important; height: auto !important; display: block; margin: 15px auto; border: 4px solid #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
            .ngt-qr-toggle-btn { display: block; width: 100%; text-align: center; margin: 15px 0; padding: 10px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; color: #334155; font-size: 13px; font-weight: 500; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
            .ngt-qr-toggle-btn:hover { background: #f8fafc; }
        </style>
        <?php
    }

    public function inject_qr_login_toggle_button() {
        $qr_session_token = wp_generate_password( 32, false );
        set_transient( 'ngt_qr_auth_' . $qr_session_token, 'pending', 300 );
        
        $qr_image_url = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($qr_session_token);
        ?>
        <div class="ngt-qr-wrapper-panel">
            <button type="button" id="ngt_toggle_qr_view" class="ngt-qr-toggle-btn">📲 <?php _e('Log In with Mobile QR Scan', 'ngt-easy-login-customizer'); ?></button>
            
            <div id="ngt_qr_container_box" class="ngt-qr-container" style="display:none; text-align:center;">
                <p class="description" style="margin-bottom:10px; font-size: 12px; color: #64748b;"><?php _e('Scan token via verified secure API app to sign in instantly.', 'ngt-easy-login-customizer'); ?></p>
                <img src="<?php echo esc_url($qr_image_url); ?>" alt="QR Portal Token" />
                <div class="ngt-spinner-loader" style="font-size:12px; color:#64748b;"><?php _e('Waiting for secure authentication...', 'ngt-easy-login-customizer'); ?></div>
            </div>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var token = "<?php echo esc_js($qr_session_token); ?>";
                var pollInterval;

                $('#ngt_toggle_qr_view').click(function(e) {
                    e.preventDefault();
                    $('#ngt_qr_container_box').slideToggle(200);
                    $('#loginform > p:not(.ngt-qr-wrapper-panel), .forgetmenot, .wp-user-form-submit-wrapper').toggle();
                    
                    if ($('#ngt_qr_container_box').is(':visible')) {
                        pollInterval = setInterval(checkSessionAuthorization, 2000);
                    } else {
                        clearInterval(pollInterval);
                    }
                });

                function checkSessionAuthorization() {
                    $.ajax({
                        url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
                        method: "POST",
                        data: { 
                            action: 'ngt_check_qr_session',
                            token: token 
                        },
                        success: function(response) {
                            if (response && response.success && response.data.status === 'approved') {
                                clearInterval(pollInterval);
                                $('.ngt-spinner-loader').text('🔒 Authenticated! Redirecting...');
                                window.location.href = response.data.redirect_url;
                            }
                        }
                    });
                }
            });
        </script>
        <?php
    }

    public function register_qr_endpoint_routes() {
        register_rest_route( 'ngt/v1', '/approve-qr', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'api_authorize_token' ),
            'permission_callback' => '__return_true'
        ));
    }

    public function ajax_verify_token_status_fallback() {
        $token  = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        $status = get_transient( 'ngt_qr_auth_' . $token );

        if ( is_numeric( $status ) ) {
            wp_set_current_user( $status );
            wp_set_auth_cookie( $status, true );
            delete_transient( 'ngt_qr_auth_' . $token );
            
            wp_send_json_success( array(
                'status'       => 'approved',
                'redirect_url' => admin_url()
            ) );
        }

        wp_send_json_error( array( 'status' => 'pending' ) );
    }

    public function api_authorize_token( $request ) {
        $token   = sanitize_text_field( $request->get_param('token') );
        $user_id = absint( $request->get_param('user_id') );
        $incoming_signature = $request->get_header('X-NGT-Signature');

        if ( empty($token) || empty($user_id) || empty($incoming_signature) ) {
            return new WP_REST_Response( array( 'success' => false, 'message' => 'Access Denied: Missing parameters.' ), 400 );
        }

        $expected_signature = hash_hmac( 'sha256', $token . $user_id, $this->api_secret_key );

        if ( ! hash_equals( $expected_signature, $incoming_signature ) ) {
            return new WP_REST_Response( array( 'success' => false, 'message' => 'Access Denied: Invalid authentication signature hash.' ), 401 );
        }

        if ( get_transient( 'ngt_qr_auth_' . $token ) === 'pending' ) {
            set_transient( 'ngt_qr_auth_' . $token, $user_id, 60 );
            return new WP_REST_Response( array( 'success' => true, 'message' => 'Signature verified. Desktop authenticated successfully.' ), 200 );
        }

        return new WP_REST_Response( array( 'success' => false, 'message' => 'Token has expired or is invalid.' ), 403 );
    }
}