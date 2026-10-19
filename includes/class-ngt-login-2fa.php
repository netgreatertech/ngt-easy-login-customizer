<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class NGT_Login_2FA {
    public function __construct() {
        // DISABLED FOR NOW: Bypasses 2FA logic completely on the frontend login flow
        /*
        if ( (int) get_option('ngt_enable_2fa_engine', 0) === 1 ) {
            // Intercept standard authentication flow
            add_filter( 'wp_authenticate_user', array( $this, 'intercept_login_for_2fa' ), 99, 2 );
        }
        */
    }

    /**
     * Intercepts the authentication procedure to verify TOTP tokens natively
     */
    public function intercept_login_for_2fa( $user, $password ) {
        if ( is_wp_error( $user ) || isset( $_POST['ngt_2fa_bypass_verified'] ) ) {
            return $user;
        }

        $submitted_code = isset( $_POST['ngt_2fa_code'] ) ? sanitize_text_field( $_POST['ngt_2fa_code'] ) : '';

        if ( empty( $submitted_code ) ) {
            // Step 1 Passed, now halt standard login and prompt user for the 2FA Code
            $this->render_2fa_verification_screen( $_POST['log'], $_POST['pwd'] );
            exit;
        }

        if ( ! $this->verify_native_totp_code( $user->ID, $submitted_code ) ) {
            return new WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>: Invalid 2FA verification code.', 'ngt-easy-login-customizer' ) );
        }

        return $user;
    }

    /**
     * Halts standard screen and redraws the verification input prompt seamlessly
     */
    private function render_2fa_verification_screen( $username, $password ) {
        // Handle UI injection for step 2 code input field inside customizer
        add_action('login_form', function() use ($username, $password) {
            ?>
            <div class="user-pass-fields-2fa" style="margin-top: 15px; margin-bottom: 15px;">
                <label for="ngt_2fa_code" style="display: block; margin-bottom: 5px; font-weight: 500; color: #334155;"><?php _e('Enter 6-Digit Verification Code', 'ngt-easy-login-customizer'); ?></label>
                <input type="text" name="ngt_2fa_code" id="ngt_2fa_code" class="input" value="" size="20" autocomplete="off" placeholder="000000" style="text-align:center; font-size:20px; letter-spacing:4px; padding: 12px; border-radius: 8px; width: 100%; box-sizing: border-box;" required />
            </div>
            <input type="hidden" name="log" value="<?php echo esc_attr($username); ?>" />
            <input type="hidden" name="pwd" value="<?php echo esc_attr($password); ?>" />
            <input type="hidden" name="ngt_2fa_bypass_verified" value="1" />
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    // Hide the username and password fields during step 2 verification
                    $('#loginform > p:not(.user-pass-fields-2fa), .user-pass-fields, .ngt-qr-wrapper-panel, .forgetmenot').hide();
                });
            </script>
            <?php
        });
    }

    private function verify_native_totp_code( $user_id, $code ) {
        $secret = get_user_meta( $user_id, 'ngt_2fa_secret_key', true );
        if ( empty( $secret ) ) {
            return true; // Fallback if user secret is not configured
        }

        $time_window = floor( time() / 30 );
        for ( $i = -1; $i <= 1; $i++ ) {
            if ( $this->calculate_totp_checksum( $secret, $time_window + $i ) === $code ) {
                return true;
            }
        }
        return false;
    }

    private function calculate_totp_checksum( $secret, $time ) {
        $time_pack = pack( 'N*', 0 ) . pack( 'N*', $time );
        
        // Helper function fallback if base32 decoding is needed natively
        $key_pack  = ( function_exists('base32_decode') ) ? base32_decode( $secret ) : $secret; 
        $hash_pack = hash_hmac( 'sha1', $time_pack, $key_pack, true );
        $offset    = ord( $hash_pack[19] ) & 0xf;
        
        $checksum = (
            ( ord( $hash_pack[ $offset + 0 ] ) & 0x7f ) << 24 |
            ( ord( $hash_pack[ $offset + 1 ] ) & 0xff ) << 16 |
            ( ord( $hash_pack[ $offset + 2 ] ) & 0xff ) << 8  |
            ( ord( $hash_pack[ $offset + 3 ] ) & 0xff )
        ) % 1000000;

        return str_pad( $checksum, 6, '0', STR_PAD_LEFT );
    }
}