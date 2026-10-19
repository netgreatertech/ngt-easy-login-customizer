<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class NGT_Login_Customiser {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_ngt_sidebar_menu' ) );
        add_action( 'admin_init', array( $this, 'register_ngt_portal_settings' ) );
        add_action( 'login_enqueue_scripts', array( $this, 'compile_and_output_css_and_scripts' ) );
        
        add_filter( 'login_redirect', array( $this, 'handle_custom_role_redirects' ), 10, 3 );
        add_filter( 'login_headerurl', function() { return home_url(); } );
        add_filter( 'login_headertext', function() { return get_bloginfo( 'name' ); } );
    }

    public function register_ngt_sidebar_menu() {
        add_menu_page(
            __( 'NGT Login Setting', 'ngt-identity-portal-suite' ), 
            __( 'NGT Login Setting', 'ngt-identity-portal-suite' ),       
            'manage_options',
            'ngt-admin-login-setting',
            array( $this, 'render_ngt_settings_page' ),
            'dashicons-shield',                                           
            61 
        );
    }

    public function register_ngt_portal_settings() {
        register_setting( 'ngt_portal_settings_group', 'ngt_gateway_secret', 'sanitize_key' );
        register_setting( 'ngt_portal_settings_group', 'ngt_custom_login_slug', 'sanitize_title' );

        // Layout settings
        register_setting( 'ngt_portal_settings_group', 'ngt_layout_style', 'sanitize_text_field' );
        register_setting( 'ngt_portal_settings_group', 'ngt_form_position', 'sanitize_text_field' );
        
        // 1-Column Background Settings
        register_setting( 'ngt_portal_settings_group', 'ngt_bg_type', 'sanitize_text_field' );
        register_setting( 'ngt_portal_settings_group', 'ngt_base_backdrop', 'sanitize_hex_color' );
        register_setting( 'ngt_portal_settings_group', 'ngt_gradient_end_color', 'sanitize_hex_color' );
        register_setting( 'ngt_portal_settings_group', 'ngt_gradient_type', 'sanitize_text_field' );
        register_setting( 'ngt_portal_settings_group', 'ngt_gradient_angle', 'sanitize_text_field' );
        register_setting( 'ngt_portal_settings_group', 'ngt_portal_bg_image', 'esc_url_raw' );

        // 2-Column Background Settings
        register_setting( 'ngt_portal_settings_group', 'ngt_form_side_bg_type', 'sanitize_text_field' );
        register_setting( 'ngt_portal_settings_group', 'ngt_form_side_base_color', 'sanitize_hex_color' );
        register_setting( 'ngt_portal_settings_group', 'ngt_form_side_grad_color', 'sanitize_hex_color' );
        register_setting( 'ngt_portal_settings_group', 'ngt_form_side_gradient_type', 'sanitize_text_field' );
        register_setting( 'ngt_portal_settings_group', 'ngt_form_side_gradient_angle', 'sanitize_text_field' );

        // Shared Options
        register_setting( 'ngt_portal_settings_group', 'ngt_opacity', 'sanitize_text_field' );
        register_setting( 'ngt_portal_settings_group', 'ngt_brand_interactive', 'sanitize_hex_color' );
        register_setting( 'ngt_portal_settings_group', 'ngt_label_color', 'sanitize_hex_color' ); // NEW
        register_setting( 'ngt_portal_settings_group', 'ngt_enable_glassmorphism', 'absint' );
        register_setting( 'ngt_portal_settings_group', 'ngt_enable_dark_mode', 'absint' );
        
        // Other Settings
        register_setting( 'ngt_portal_settings_group', 'ngt_portal_logo', 'esc_url_raw' );
        register_setting( 'ngt_portal_settings_group', 'ngt_logo_custom_width', 'sanitize_text_field' );
        register_setting( 'ngt_portal_settings_group', 'ngt_logo_custom_height', 'sanitize_text_field' );
        register_setting( 'ngt_portal_settings_group', 'ngt_form_custom_width', 'sanitize_text_field' );
        register_setting( 'ngt_portal_settings_group', 'ngt_form_border_radius', 'sanitize_text_field' );
        register_setting( 'ngt_portal_settings_group', 'ngt_custom_css_sandbox', 'wp_strip_all_tags' );
        register_setting( 'ngt_portal_settings_group', 'ngt_hide_extra_links', 'absint' );
        register_setting( 'ngt_portal_settings_group', 'ngt_hide_remember_me', 'absint' );
        register_setting( 'ngt_portal_settings_group', 'ngt_disable_error_shake', 'absint' );
        register_setting( 'ngt_portal_settings_group', 'ngt_subscriber_redirect_url', 'esc_url_raw' );
    }

    public function handle_custom_role_redirects( $redirect_to, $request, $user ) {
        if ( isset( $user->roles ) && is_array( $user->roles ) ) {
            $custom_url = get_option( 'ngt_subscriber_redirect_url', '' );
            if ( ! empty( $custom_url ) && ! in_array( 'administrator', $user->roles ) ) {
                return $custom_url;
            }
        }
        return $redirect_to;
    }

    public function render_ngt_settings_page() {
        wp_enqueue_media();
        
        // Global
        $login_slug    = get_option('ngt_custom_login_slug', '');
        $security_key  = get_option('ngt_gateway_secret', '');
        $site_home_url = trailingslashit(home_url());

        // Layout Variables
        $layout        = get_option('ngt_layout_style', 'one-column');
        $form_pos      = get_option('ngt_form_position', 'left-image');

        // 1-Col Variables
        $bg_type       = get_option('ngt_bg_type', 'color');
        $bg_color      = get_option('ngt_base_backdrop', '#f0f4f8');
        $bg_grad_end   = get_option('ngt_gradient_end_color', '#d9e2ec');
        $grad_type     = get_option('ngt_gradient_type', 'linear');
        $grad_angle    = get_option('ngt_gradient_angle', '135deg');
        $bg_image      = get_option('ngt_portal_bg_image', '');

        // 2-Col Variables
        $form_bg_type     = get_option('ngt_form_side_bg_type', 'color');
        $form_side_color  = get_option('ngt_form_side_base_color', '#ffffff');
        $form_side_grad   = get_option('ngt_form_side_grad_color', '#f1f5f9');
        $form_grad_type   = get_option('ngt_form_side_gradient_type', 'linear');
        $form_grad_angle  = get_option('ngt_form_side_gradient_angle', '135deg');

        // Shared/Misc Variables
        $opacity       = get_option('ngt_opacity', '1');
        $brand_tint    = get_option('ngt_brand_interactive', '#0052cc');
        $label_color   = get_option('ngt_label_color', '#334155'); // NEW
        $width         = get_option('ngt_form_custom_width', '380px');
        $border_radius = get_option('ngt_form_border_radius', '16px');
        $custom_css    = get_option('ngt_custom_css_sandbox', '');
        $logo_width    = get_option('ngt_logo_custom_width', '100%');
        $logo_height   = get_option('ngt_logo_custom_height', '90px');
        ?>
        <div class="wrap">
            <h1 style="font-weight: 700; color: #1e293b; margin-bottom: 5px;"><?php _e( 'NGT Admin Login Setting', 'ngt-identity-portal-suite' ); ?></h1>
            <p style="color: #64748b; margin-top: 0; font-size: 14px;"><?php _e( 'Configure gateway security keys, dual-layer routing URLs, and custom identity parameters.', 'ngt-identity-portal-suite' ); ?></p>
            
            <!-- Donation / Support Banner -->
            <div style="background: #fff; border: 1px solid #e5e5e5; padding: 20px 25px; border-radius: 8px; margin-top: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 4px solid #91C13D;">
                <div>
                    <h3 style="margin: 0 0 5px 0; font-size: 16px; color: #112837;">
                        <span class="dashicons dashicons-heart" style="color: #d63638; vertical-align: text-bottom; margin-right: 5px;"></span> 
                        <?php _e( 'Support the Development', 'ngt-identity-portal-suite' ); ?>
                    </h3>
                    <p style="margin: 0; color: #50575e; font-size: 14px;">
                        <?php _e( 'If this plugin adds value to your business, consider supporting our ongoing updates and maintenance!', 'ngt-identity-portal-suite' ); ?>
                    </p>
                </div>
                <div>
                    <!-- Replace the href '#' with your actual payment link -->
                    <a href="https://paypal.me/netgreater?locale.x=en_GB&country.x=IN" target="_blank" style="background: #91C13D; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: 600; display: inline-block; font-size: 14px; transition: opacity 0.2s;">
                        <?php _e( 'Donate Now', 'ngt-identity-portal-suite' ); ?> &rarr;
                    </a>
                </div>
            </div>

            <form method="post" action="options.php" style="background: #ffffff; padding: 35px; border-radius: 12px; margin-top: 25px; max-width: 850px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border: 1px solid #e2e8f0;">
                <?php settings_fields( 'ngt_portal_settings_group' ); ?>
            
            <form method="post" action="options.php" style="background: #ffffff; padding: 35px; border-radius: 12px; margin-top: 25px; max-width: 850px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border: 1px solid #e2e8f0;">
                <?php settings_fields( 'ngt_portal_settings_group' ); ?>
                
                <table class="form-table" role="presentation">
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Custom Login URL Slug', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <span style="font-family: monospace; color: #64748b; font-size: 14px;"><?php echo esc_url($site_home_url); ?></span>
                            <input type="text" id="ngt_custom_login_slug" name="ngt_custom_login_slug" value="<?php echo esc_attr($login_slug); ?>" class="regular-text" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; width: 180px;" placeholder="e.g. portal" />
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Dynamic Security Key', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="text" id="ngt_gateway_secret" name="ngt_gateway_secret" value="<?php echo esc_attr($security_key); ?>" class="regular-text" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1;" placeholder="e.g. staff" />
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-top: 10px; font-family: monospace; font-size: 13px; color: #0f172a;">
                                <strong><?php _e('Your Active Portal URL Pathway:', 'ngt-identity-portal-suite'); ?></strong><br/>
                                <span id="ngt_url_live_preview" style="color: #0052cc; word-break: break-all;"></span>
                            </div>
                        </td>
                    </tr>

                    <!-- PAGE LAYOUT -->
                    <tr>
                        <td colspan="2" style="padding: 10px 0;"><hr style="border:0; border-top:1px solid #e2e8f0;" /><h3 style="margin-top:10px; color:#0f172a; font-weight:600;"><?php _e('🔲 Page Layout Design', 'ngt-identity-portal-suite'); ?></h3></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Select Layout', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <select name="ngt_layout_style" id="ngt_layout_style" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; min-width: 250px;">
                                <option value="one-column" <?php selected($layout, 'one-column'); ?>><?php _e('One Column (Centered Box)', 'ngt-identity-portal-suite'); ?></option>
                                <option value="two-column" <?php selected($layout, 'two-column'); ?>><?php _e('Two Column (Split Screen)', 'ngt-identity-portal-suite'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr valign="top" class="2col-only">
                        <th scope="row" style="font-weight: 600; color: #0052cc;"><?php _e( 'Split Layout Position', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <select name="ngt_form_position" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; min-width: 250px;">
                                <option value="left-image" <?php selected($form_pos, 'left-image'); ?>><?php _e('Image on Left / Form on Right', 'ngt-identity-portal-suite'); ?></option>
                                <option value="right-image" <?php selected($form_pos, 'right-image'); ?>><?php _e('Image on Right / Form on Left', 'ngt-identity-portal-suite'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <!-- BACKGROUND & COLORS -->
                    <tr>
                        <td colspan="2" style="padding: 10px 0;"><hr style="border:0; border-top:1px solid #e2e8f0;" /><h3 style="margin-top:10px; color:#0f172a; font-weight:600;"><?php _e('🎨 Background & Colors', 'ngt-identity-portal-suite'); ?></h3></td>
                    </tr>
                    
                    <!-- USER FRIENDLY 2-COLUMN HEADER: IMAGE HALF -->
                    <tr class="2col-only"><td colspan="2" style="padding: 0;"><h4 style="margin: 15px 0 5px 0; color: #0052cc; font-size: 14px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 5px;">🖼️ Image Half Settings</h4></td></tr>

                    <!-- Image Upload -->
                    <tr valign="top" class="1col-image 2col-only">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Upload Background Image', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="text" id="ngt_portal_bg_image" name="ngt_portal_bg_image" value="<?php echo esc_url($bg_image); ?>" class="regular-text" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; width: 250px;" />
                            <input type="button" id="ngt_upload_bg_btn" class="button button-secondary" value="<?php _e( 'Select Media', 'ngt-identity-portal-suite' ); ?>" style="padding: 4px 14px; height: 35px;"/>
                        </td>
                    </tr>

                    <!-- 1-Column Background Type -->
                    <tr valign="top" class="1col-only">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Background Type', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <select name="ngt_bg_type" id="ngt_bg_type" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; min-width: 250px;">
                                <option value="color" <?php selected($bg_type, 'color'); ?>><?php _e('Solid Color', 'ngt-identity-portal-suite'); ?></option>
                                <option value="gradient" <?php selected($bg_type, 'gradient'); ?>><?php _e('Beautiful Linear Gradient', 'ngt-identity-portal-suite'); ?></option>
                                <option value="image" <?php selected($bg_type, 'image'); ?>><?php _e('Background Image', 'ngt-identity-portal-suite'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <!-- USER FRIENDLY 2-COLUMN HEADER: FORM HALF -->
                    <tr class="2col-only"><td colspan="2" style="padding: 0;"><h4 style="margin: 15px 0 5px 0; color: #0052cc; font-size: 14px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 5px;">📝 Form Panel Settings</h4></td></tr>

                    <!-- 2-Column Background Type -->
                    <tr valign="top" class="2col-only">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Panel Background Type', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <select name="ngt_form_side_bg_type" id="ngt_form_side_bg_type" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; min-width: 250px;">
                                <option value="color" <?php selected($form_bg_type, 'color'); ?>><?php _e('Solid Color', 'ngt-identity-portal-suite'); ?></option>
                                <option value="gradient" <?php selected($form_bg_type, 'gradient'); ?>><?php _e('Beautiful Linear Gradient', 'ngt-identity-portal-suite'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <!-- 1-Col Color Pickers -->
                    <tr valign="top" class="1col-only 1col-color">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Background Color 1', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="color" name="ngt_base_backdrop" value="<?php echo esc_attr( $bg_color ); ?>" style="border: none; width: 50px; height: 30px; border-radius: 4px; cursor: pointer;" />
                        </td>
                    </tr>
                    <tr valign="top" class="1col-only 1col-gradient">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Background Color 2', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="color" name="ngt_gradient_end_color" value="<?php echo esc_attr( $bg_grad_end ); ?>" style="border: none; width: 50px; height: 30px; border-radius: 4px; cursor: pointer;" />
                        </td>
                    </tr>
                    <tr valign="top" class="1col-only 1col-gradient">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Gradient Setup', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <select name="ngt_gradient_type" style="padding: 0 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; height: 36px;">
                                    <option value="linear" <?php selected($grad_type, 'linear'); ?>>Linear</option>
                                    <option value="radial" <?php selected($grad_type, 'radial'); ?>>Radial</option>
                                </select>
                                <input type="text" name="ngt_gradient_angle" value="<?php echo esc_attr($grad_angle); ?>" placeholder="135deg" style="padding: 0 12px; border-radius: 6px; border: 1px solid #cbd5e1; text-align: center; width: 90px; height: 36px; box-sizing: border-box;" />
                            </div>
                        </td>
                    </tr>

                    <!-- 2-Col Color Pickers -->
                    <tr valign="top" class="2col-only 2col-color">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Panel Color 1', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="color" name="ngt_form_side_base_color" value="<?php echo esc_attr( $form_side_color ); ?>" style="border: none; width: 50px; height: 30px; border-radius: 4px; cursor: pointer;" />
                        </td>
                    </tr>
                    <tr valign="top" class="2col-only 2col-gradient">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Panel Color 2', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="color" name="ngt_form_side_grad_color" value="<?php echo esc_attr( $form_side_grad ); ?>" style="border: none; width: 50px; height: 30px; border-radius: 4px; cursor: pointer;" />
                        </td>
                    </tr>
                    <tr valign="top" class="2col-only 2col-gradient">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Gradient Setup', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <select name="ngt_form_side_gradient_type" style="padding: 0 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; height: 36px;">
                                    <option value="linear" <?php selected($form_grad_type, 'linear'); ?>>Linear</option>
                                    <option value="radial" <?php selected($form_grad_type, 'radial'); ?>>Radial</option>
                                </select>
                                <input type="text" name="ngt_form_side_gradient_angle" value="<?php echo esc_attr($form_grad_angle); ?>" placeholder="135deg" style="padding: 0 12px; border-radius: 6px; border: 1px solid #cbd5e1; text-align: center; width: 90px; height: 36px; box-sizing: border-box;" />
                            </div>
                        </td>
                    </tr>

                    <!-- USER FRIENDLY GLOBAL OVERRIDES HEADER -->
                    <tr><td colspan="2" style="padding: 0;"><h4 style="margin: 15px 0 5px 0; color: #0052cc; font-size: 14px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 5px;">🌟 Visual Enhancements</h4></td></tr>

                    <!-- OPACITY SLIDER -->
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Background Visibility (Fade)', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <input type="range" name="ngt_opacity" id="ngt_opacity_slider" min="0" max="1" step="0.01" value="<?php echo esc_attr( $opacity ); ?>" style="width: 200px;" />
                                <span id="ngt_opacity_display" style="background: #f8fafc; padding: 4px 12px; border-radius: 4px; border: 1px solid #cbd5e1; font-weight: 600; min-width: 50px; text-align: center;">
                                    <?php echo round( floatval($opacity) * 100 ); ?>%
                                </span>
                            </div>
                            <p class="description" style="margin-top: 8px;">Slide to fade the background layer. <strong>100%</strong> is fully visible, lower values blend the background smoothly into the page base color.</p>
                        </td>
                    </tr>

                    <!-- NEW LABEL COLOR PICKER -->
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Text Label Color', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="color" name="ngt_label_color" value="<?php echo esc_attr( $label_color ); ?>" style="border: none; width: 50px; height: 30px; border-radius: 4px; cursor: pointer;" />
                            <span class="description" style="margin-left: 5px;">Changes the color of "Username", "Password", etc. Change to light if using Dark Mode!</span>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Button & Input Focus Color', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="color" name="ngt_brand_interactive" value="<?php echo esc_attr( $brand_tint ); ?>" style="border: none; width: 50px; height: 30px; border-radius: 4px; cursor: pointer;" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Enable Frosted Glass Effect', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="checkbox" name="ngt_enable_glassmorphism" value="1" <?php checked( get_option('ngt_enable_glassmorphism'), 1 ); ?> />
                            <span class="description" style="margin-left: 5px;">Applies blur to the login form container.</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Force Dark Mode Theme', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="checkbox" name="ngt_enable_dark_mode" value="1" <?php checked( get_option('ngt_enable_dark_mode'), 1 ); ?> />
                        </td>
                    </tr>

                    <!-- LOGO BRANDING -->
                    <tr>
                        <td colspan="2" style="padding: 10px 0;"><hr style="border:0; border-top:1px solid #e2e8f0;" /><h3 style="margin-top:10px; color:#0f172a; font-weight:600;"><?php _e('🖼️ Identity Logo Branding', 'ngt-identity-portal-suite'); ?></h3></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Identity Branding Logo', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="text" id="ngt_portal_logo" name="ngt_portal_logo" value="<?php echo esc_url( get_option('ngt_portal_logo') ); ?>" class="regular-text" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; width: 350px;" />
                            <input type="button" id="ngt_upload_logo_btn" class="button button-secondary" style="padding: 4px 14px; height: 35px; font-weight: 500; margin-left: 5px;" value="<?php _e( 'Select Asset Image', 'ngt-identity-portal-suite' ); ?>" />
                            <div style="margin-top: 15px;">
                                <img id="ngt_logo_preview" src="<?php echo esc_url( get_option('ngt_portal_logo') ); ?>" style="max-height: 80px; display: <?php echo get_option('ngt_portal_logo') ? 'block' : 'none'; ?>; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0;" />
                            </div>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Custom Logo Dimensions', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div>
                                    <span style="color:#64748b; font-size:13px; margin-right:5px;">Width:</span>
                                    <input type="text" name="ngt_logo_custom_width" value="<?php echo esc_attr( $logo_width ); ?>" placeholder="100%" style="padding: 0 12px; border-radius: 6px; border: 1px solid #cbd5e1; text-align:center; width: 90px; height: 36px; box-sizing: border-box;" />
                                </div>
                                <div>
                                    <span style="color:#64748b; font-size:13px; margin-right:5px;">Height:</span>
                                    <input type="text" name="ngt_logo_custom_height" value="<?php echo esc_attr( $logo_height ); ?>" placeholder="90px" style="padding: 0 12px; border-radius: 6px; border: 1px solid #cbd5e1; text-align:center; width: 90px; height: 36px; box-sizing: border-box;" />
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- MULTI-FACTOR -->
                    <tr>
                        <td colspan="2" style="padding: 10px 0;"><hr style="border:0; border-top:1px solid #e2e8f0;" /><h3 style="margin-top:10px; color:#0f172a; font-weight:600;"><?php _e('🔐 Multi-Factor Identity Extensions', 'ngt-identity-portal-suite'); ?></h3></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #94a3b8;"><?php _e( 'Enable QR Code Authentication', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="checkbox" name="ngt_enable_qr_engine_disabled" value="0" disabled style="opacity: 0.5;" />
                            <span style="margin-left: 8px; background: #f1f5f9; color: #64748b; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 12px; border: 1px solid #cbd5e1;">⏱️ Coming Soon</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #94a3b8;"><?php _e( 'Activate Two-Factor Authentication (2FA)', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="checkbox" name="ngt_enable_2fa_engine_disabled" value="0" disabled style="opacity: 0.5;" />
                            <span style="margin-left: 8px; background: #f1f5f9; color: #64748b; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 12px; border: 1px solid #cbd5e1;">⏱️ Coming Soon</span>
                        </td>
                    </tr>

                    <!-- REDIRECTS -->
                    <tr>
                        <td colspan="2" style="padding: 10px 0;"><hr style="border:0; border-top:1px solid #e2e8f0;" /><h3 style="margin-top:10px; color:#0f172a; font-weight:600;"><?php _e('🔥 Premium Post-Login Redirects', 'ngt-identity-portal-suite'); ?></h3></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Client / Non-Admin Destination', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="text" name="ngt_subscriber_redirect_url" value="<?php echo esc_url( get_option('ngt_subscriber_redirect_url') ); ?>" class="regular-text" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1;" placeholder="https://example.com/dashboard" />
                        </td>
                    </tr>

                    <!-- LOGIN BOX SIZING -->
                    <tr>
                        <td colspan="2" style="padding: 10px 0;"><hr style="border:0; border-top:1px solid #e2e8f0;" /><h3 style="margin-top:10px; color:#0f172a; font-weight:600;"><?php _e('Login Box Size', 'ngt-identity-portal-suite'); ?></h3></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Login Box Width', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="text" name="ngt_form_custom_width" value="<?php echo esc_attr( $width ); ?>" style="padding: 0 12px; border-radius: 6px; border: 1px solid #cbd5e1; text-align:center; width: 100px; height: 36px; box-sizing: border-box;" placeholder="380px" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Login Box Corner Rounding', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <input type="text" name="ngt_form_border_radius" value="<?php echo esc_attr( $border_radius ); ?>" style="padding: 0 12px; border-radius: 6px; border: 1px solid #cbd5e1; text-align:center; width: 100px; height: 36px; box-sizing: border-box;" placeholder="16px" />
                        </td>
                    </tr>

                    <!-- MINIMALIST TWEAKS -->
                    <tr>
                        <td colspan="2" style="padding: 10px 0;"><hr style="border:0; border-top:1px solid #e2e8f0;" /><h3 style="margin-top:10px; color:#0f172a; font-weight:600;"><?php _e('⚙️ Minimalist Visual Layout Tweaks', 'ngt-identity-portal-suite'); ?></h3></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Hide Lost Password & Back-to-Site Links', 'ngt-identity-portal-suite' ); ?></th>
                        <td><input type="checkbox" name="ngt_hide_extra_links" value="1" <?php checked( get_option('ngt_hide_extra_links'), 1 ); ?> /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Remove "Remember Me" Checkbox', 'ngt-identity-portal-suite' ); ?></th>
                        <td><input type="checkbox" name="ngt_hide_remember_me" value="1" <?php checked( get_option('ngt_hide_remember_me'), 1 ); ?> /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Disable Error Shaking Animation', 'ngt-identity-portal-suite' ); ?></th>
                        <td><input type="checkbox" name="ngt_disable_error_shake" value="1" <?php checked( get_option('ngt_disable_error_shake'), 1 ); ?> /></td>
                    </tr>

                    <!-- CUSTOM CSS -->
                    <tr>
                        <td colspan="2" style="padding: 10px 0;"><hr style="border:0; border-top:1px solid #e2e8f0;" /><h3 style="margin-top:10px; color:#0f172a; font-weight:600;"><?php _e('Developer Custom CSS Rules', 'ngt-identity-portal-suite'); ?></h3></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; color: #334155;"><?php _e( 'Custom CSS Overrides', 'ngt-identity-portal-suite' ); ?></th>
                        <td>
                            <textarea name="ngt_custom_css_sandbox" rows="6" cols="60" style="font-family:Consolas, Monaco, monospace; font-size:12px; background:#1e293b; color:#f8fafc; padding:15px; border-radius:8px; width:100%; box-sizing:border-box;" placeholder="/* Custom CSS Rules */"><?php echo esc_textarea( $custom_css ); ?></textarea>
                        </td>
                    </tr>
                </table>

                <div style="margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                    <?php submit_button( __( 'Save Setting Changes', 'ngt-identity-portal-suite' ), 'primary', 'submit', false, array('style' => 'padding: 8px 24px; font-size: 14px; height: auto; border-radius: 6px;') ); ?>
                </div>
            </form>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($){
                var siteHome = "<?php echo esc_url($site_home_url); ?>";
                
                function compileUrlPreview() {
                    var slug = $('#ngt_custom_login_slug').val().trim();
                    var key  = $('#ngt_gateway_secret').val().trim();
                    var basePath = slug ? slug : "wp-login.php";
                    var compiledUrl = siteHome + basePath;
                    if (key) { compiledUrl += "?" + key; }
                    $('#ngt_url_live_preview').text(compiledUrl);
                }

                $('#ngt_custom_login_slug, #ngt_gateway_secret').on('input change', function() {
                    compileUrlPreview();
                });

                compileUrlPreview();

                // Opacity Slider Real-Time Number Update
                $('#ngt_opacity_slider').on('input', function() {
                    $('#ngt_opacity_display').text( Math.round($(this).val() * 100) + '%' );
                });

                // UI Layout Toggles
                function toggleLayoutUI() {
                    var layout = $('#ngt_layout_style').val();
                    
                    if (layout === 'one-column') {
                        $('.2col-only').hide();
                        $('.1col-only').show();
                        
                        var bg1Col = $('#ngt_bg_type').val();
                        $('.1col-color').toggle(bg1Col === 'color' || bg1Col === 'gradient');
                        $('.1col-gradient').toggle(bg1Col === 'gradient');
                        $('.1col-image').toggle(bg1Col === 'image');
                    } else {
                        $('.1col-only').hide();
                        $('.2col-only').show();
                        
                        var bg2Col = $('#ngt_form_side_bg_type').val();
                        $('.2col-color').toggle(bg2Col === 'color' || bg2Col === 'gradient');
                        $('.2col-gradient').toggle(bg2Col === 'gradient');
                    }
                }
                
                $('#ngt_layout_style, #ngt_bg_type, #ngt_form_side_bg_type').change(toggleLayoutUI);
                toggleLayoutUI();

                // Uploaders
                $('#ngt_upload_logo_btn').click(function(e) {
                    e.preventDefault();
                    var logoUploader = wp.media({ title: 'Select Custom Portal Logo', multiple: false }).open().on('select', function() {
                        var asset = logoUploader.state().get('selection').first().toJSON();
                        $('#ngt_portal_logo').val(asset.url);
                        $('#ngt_logo_preview').attr('src', asset.url).show();
                    });
                });

                $('#ngt_upload_bg_btn').click(function(e) {
                    e.preventDefault();
                    var bgUploader = wp.media({ title: 'Select Backdrop Image Canvas', multiple: false }).open().on('select', function() {
                        var asset = bgUploader.state().get('selection').first().toJSON();
                        $('#ngt_portal_bg_image').val(asset.url);
                    });
                });
            });
        </script>
        <?php
    }

    public function compile_and_output_css_and_scripts() {
        wp_enqueue_script( 'jquery' );

        // 1-Col Variables
        $bg_type       = get_option( 'ngt_bg_type', 'color' );
        $bg_canvas     = get_option( 'ngt_base_backdrop', '#f0f4f8' );
        $grad_end      = get_option( 'ngt_gradient_end_color', '#d9e2ec' );
        $grad_type     = get_option( 'ngt_gradient_type', 'linear' );
        $grad_angle    = get_option( 'ngt_gradient_angle', '135deg' );
        $bg_image      = get_option( 'ngt_portal_bg_image', '' );

        // 2-Col Variables
        $layout             = get_option( 'ngt_layout_style', 'one-column' );
        $form_pos           = get_option( 'ngt_form_position', 'left-image' );
        $form_side_bg_type  = get_option( 'ngt_form_side_bg_type', 'color' );
        $form_side_color    = get_option( 'ngt_form_side_base_color', '#ffffff' );
        $form_side_grad     = get_option( 'ngt_form_side_grad_color', '#f1f5f9' );
        $form_grad_type     = get_option( 'ngt_form_side_gradient_type', 'linear' );
        $form_grad_angle    = get_option( 'ngt_form_side_gradient_angle', '135deg' );

        // Generate Gradient CSS Strings
        $bg_grad_css = ($grad_type === 'radial') 
            ? "radial-gradient(circle, {$bg_canvas}, {$grad_end})" 
            : "linear-gradient({$grad_angle}, {$bg_canvas}, {$grad_end})";

        $form_grad_css = ($form_grad_type === 'radial') 
            ? "radial-gradient(circle, {$form_side_color}, {$form_side_grad})" 
            : "linear-gradient({$form_grad_angle}, {$form_side_color}, {$form_side_grad})";

        // Shared Variables
        $opacity_raw   = get_option( 'ngt_opacity', '1' );
        $opacity       = ($opacity_raw === '') ? '1' : $opacity_raw;
        
        $brand_tint    = get_option( 'ngt_brand_interactive', '#0052cc' );
        $label_color   = get_option( 'ngt_label_color', '#334155' );
        $custom_logo   = get_option( 'ngt_portal_logo', '' );
        $is_glass      = get_option( 'ngt_enable_glassmorphism', 0 );
        $is_dark       = get_option( 'ngt_enable_dark_mode', 0 );
        
        $width_raw     = get_option( 'ngt_form_custom_width', '380px' );
        $card_width    = empty( trim($width_raw) ) ? '380px' : trim($width_raw);
        
        $border_radius = get_option( 'ngt_form_border_radius', '16px' );
        $sandbox_css   = get_option( 'ngt_custom_css_sandbox', '' );
        
        $hide_links    = get_option( 'ngt_hide_extra_links', 0 );
        $hide_remember = get_option( 'ngt_hide_remember_me', 0 );
        $no_shake      = get_option( 'ngt_disable_error_shake', 0 );
        $logo_w        = get_option( 'ngt_logo_custom_width', '100%' );
        $logo_h        = get_option( 'ngt_logo_custom_height', '90px' );
        ?>
        <style type="text/css">

    /* 1. SOLID BASE LAYER - Protects opacity fading */
    body.login { 
        display: block !important;
        min-height: 100vh !important;
        margin: 0 !important;
        padding: 0 !important;
        background-color: <?php echo $is_dark ? '#0f172a' : '#f8fafc'; ?> !important; 
    }

    /* 2. BACKGROUND / IMAGE LAYER (Applies Opacity Without Affecting Form) */
    <?php if ( $layout === 'one-column' ) : ?>
        body.login::before {
            content: '';
            position: fixed !important;
            top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
            z-index: 0 !important;
            opacity: <?php echo esc_attr($opacity); ?> !important;
            <?php if ( 'gradient' === $bg_type ) : ?>
                background: <?php echo $bg_grad_css; ?> !important;
            <?php elseif ( 'image' === $bg_type && !empty($bg_image) ) : ?>
                background-image: url('<?php echo esc_url($bg_image); ?>') !important;
                background-size: cover !important; 
                background-position: center !important; 
                background-repeat: no-repeat !important;
            <?php else : ?>
                background-color: <?php echo esc_attr($bg_canvas); ?> !important; 
            <?php endif; ?>
        }
    <?php else : ?>
        body.login::before {
            content: '';
            position: fixed !important;
            top: 0 !important; bottom: 0 !important;
            <?php echo ($form_pos === 'right-image') ? 'right: 0 !important; width: 50% !important;' : 'left: 0 !important; width: 50% !important;'; ?>
            background-image: url('<?php echo esc_url($bg_image); ?>') !important;
            background-size: cover !important; 
            background-position: center !important; 
            background-repeat: no-repeat !important;
            opacity: <?php echo esc_attr($opacity); ?> !important;
            z-index: 0 !important;
        }
    <?php endif; ?>
    
    /* 3. THE FORM CONTAINER (Strict widths to prevent squishing) */
    #login { 
        <?php if ( $layout !== 'one-column' ) : ?>
            position: absolute !important;
            top: 0 !important; bottom: 0 !important;
            <?php echo ($form_pos === 'right-image') ? 'left: 0 !important; width: 50% !important;' : 'right: 0 !important; width: 50% !important;'; ?>
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            align-items: center !important;
            margin: 0 !important;
            padding: 40px !important;
            box-sizing: border-box !important;
            z-index: 1 !important;
            <?php if ( 'gradient' === $form_side_bg_type ) : ?>
                background: <?php echo $form_grad_css; ?> !important;
            <?php else : ?>
                background-color: <?php echo esc_attr($form_side_color); ?> !important;
            <?php endif; ?>
        <?php else : ?>
            position: relative !important;
            z-index: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            align-items: center !important;
            min-height: 100vh !important;
            width: 100% !important;
            margin: 0 !important; 
            padding: 40px !important;
            box-sizing: border-box !important;
        <?php endif; ?>
    }

    #login > h1, 
    #login > form, 
    #login > #nav, 
    #login > #backtoblog {
        width: 100% !important;
        max-width: <?php echo esc_attr($card_width); ?> !important;
        box-sizing: border-box !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }
    
    /* Hide rogue elements injected by WordPress (e.g., language switcher) in split mode */
    <?php if ( $layout !== 'one-column' ) : ?>
        .clear, .language-switcher { display: none !important; }
    <?php endif; ?>

    <?php if ( ! empty( $custom_logo ) ) : ?>
    body.login h1 a { 
        background-image: url('<?php echo esc_url($custom_logo); ?>') !important; 
        background-size: contain !important; background-position: center bottom !important; 
        width: <?php echo esc_attr($logo_w); ?> !important; 
        height: <?php echo esc_attr($logo_h); ?> !important; 
        margin: 0 auto 30px auto !important; 
        display: block !important;
    }
    <?php endif; ?>

    /* 4. THE FORM BOX */
    .login form { 
        border: none !important; 
        border-radius: <?php echo esc_attr($border_radius); ?> !important; 
        padding: 40px 30px !important; 
        
        <?php if ( $is_glass ) : ?>
            background: rgba(255, 255, 255, 0.25) !important;
            backdrop-filter: blur(12px) !important; -webkit-backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.07) !important;
        <?php elseif ( $is_dark ) : ?>
            background: #1e293b !important; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3) !important;
        <?php else : ?>
            background: #ffffff !important; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
        <?php endif; ?>
    }
    
    .login label { 
        color: <?php echo esc_attr($label_color); ?> !important; 
        font-weight: 600 !important; 
        display: block !important;
        margin-bottom: 8px !important;
    }
    
    .wp-pwd {
        position: relative !important;
        width: 100% !important;
        display: block !important;
    }

    .login input[type="text"], 
    .login input[type="password"] { 
        background: <?php echo $is_dark ? '#334155' : '#f8fafc'; ?> !important; 
        border: 1px solid <?php echo $is_dark ? '#475569' : '#e2e8f0'; ?> !important; 
        color: <?php echo $is_dark ? '#ffffff' : '#0f172a'; ?> !important;
        border-radius: 6px !important; 
        padding: 12px 40px 12px 15px !important; 
        width: 100% !important; 
        box-sizing: border-box !important;
        font-size: 15px !important;
        box-shadow: none !important;
        margin-bottom: 20px !important;
    }
    
    .login input[type="text"]:focus, .login input[type="password"]:focus {
        border-color: <?php echo esc_attr($brand_tint); ?> !important;
        box-shadow: 0 0 0 3px <?php echo esc_attr($brand_tint); ?>33 !important;
        background: <?php echo $is_dark ? '#1e293b' : '#ffffff'; ?> !important;
    }
    
    .wp-hide-pw {
        position: absolute !important;
        right: 8px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        height: 30px !important;
        width: 30px !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        color: <?php echo $is_dark ? '#94a3b8' : '#64748b'; ?> !important;
    }

    .forgetmenot {
        margin-bottom: 25px !important;
        display: flex !important;
        align-items: center !important;
    }

    .forgetmenot input[type="checkbox"] {
        margin: 0 8px 0 0 !important;
    }

    .login .button-primary { 
        background: <?php echo esc_attr($brand_tint); ?> !important; 
        border: none !important; 
        border-radius: 6px !important; 
        width: 100% !important; 
        padding: 12px !important;
        height: auto !important; 
        font-size: 16px !important;
        font-weight: 600 !important;
        box-shadow: none !important;
        text-shadow: none !important;
        transition: opacity 0.2s ease !important;
    }
    
    .login .button-primary:hover {
        opacity: 0.9 !important;
    }

    /* 5. Mobile Responsiveness (Force back to 1 column on small screens) */
    @media only screen and (max-width: 768px) {
        /* Force hiding of the image side on mobile */
        <?php if ( $layout !== 'one-column' ) : ?>
            body.login::before { display: none !important; }
        <?php endif; ?>
        
        #login { 
            position: relative !important; 
            width: 100% !important; 
            max-width: 100% !important; 
            left: auto !important; right: auto !important; 
            min-height: 100vh !important;
            padding: 20px !important;
        }

        /* FORCE 100% WIDTH ON MOBILE (Overrides custom desktop sizes like 30%) */
        #login > h1, 
        #login > form, 
        #login > #nav, 
        #login > #backtoblog {
            width: 100% !important;
            max-width: 100% !important; 
        }
    }

    <?php if ( $hide_links ) : ?>
        #login #nav, #login #backtoblog { display: none !important; }
    <?php else : ?>
        #login #nav a, #login #backtoblog a { color: <?php echo esc_attr($label_color); ?> !important; font-size: 13px; text-decoration: none !important; }
        #login #nav a:hover, #login #backtoblog a:hover { color: <?php echo esc_attr($brand_tint); ?> !important; }
    <?php endif; ?>

    <?php if ( $hide_remember ) : ?>
        .forgetmenot { display: none !important; }
    <?php endif; ?>

    <?php if ( $no_shake ) : ?>
        .login-shake { animation: none !important; }
    <?php endif; ?>
    
    <?php if ( !empty( $sandbox_css ) ) { echo $sandbox_css; } ?>
</style>
        <?php
    }
}