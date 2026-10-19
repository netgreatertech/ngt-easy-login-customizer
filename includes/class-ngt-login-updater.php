<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class NGT_Login_Updater {
    
    private $plugin_slug;
    private $version;
    private $json_url;

    public function __construct() {
        $this->plugin_slug = 'ngt-easy-login-customizer';
        $this->version     = '1.0.0';
        $this->json_url    = 'https://netgreater.com/updates/ngt-easy-login-customizer.json';
        
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_ngt_updates' ) );
        add_filter( 'plugins_api', array( $this, 'get_ngt_plugin_info' ), 20, 3 );
    }

    /**
     * Queries your external endpoint to look for update version flags
     */
    public function check_for_ngt_updates( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $response = wp_remote_get( $this->json_url, array( 'timeout' => 15, 'sslverify' => true ) );
        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return $transient;
        }

        $remote_data = json_decode( wp_remote_retrieve_body( $response ) );
        if ( $remote_data && version_compare( $this->version, $remote_data->version, '<' ) ) {
            $plugin_obj = new stdClass();
            $plugin_obj->slug        = $this->plugin_slug;
            $plugin_obj->new_version = $remote_data->version;
            $plugin_obj->url         = $remote_data->homepage;
            $plugin_obj->package     = $remote_data->download_url;
            
            $transient->response[ $this->plugin_slug . '/' . $this->plugin_slug . '.php' ] = $plugin_obj;
        }

        return $transient;
    }

    /**
     * Injects custom text fields into the native WP update details frame overlay
     */
    public function get_ngt_plugin_info( $res, $action, $args ) {
        if ( 'plugin_information' !== $action || $this->plugin_slug !== $args->slug ) {
            return $res;
        }

        $response = wp_remote_get( $this->json_url, array( 'timeout' => 15, 'sslverify' => true ) );
        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return $res;
        }

        $remote_data = json_decode( wp_remote_retrieve_body( $response ) );
        
        $res = new stdClass();
        $res->name           = $remote_data->name;
        $res->slug           = $remote_data->slug;
        $res->version        = $remote_data->version;
        $res->author         = $remote_data->author;
        $res->homepage       = $remote_data->homepage;
        $res->download_link  = $remote_data->download_url;
        $res->sections       = array(
            'description'  => $remote_data->sections->description,
            'changelog'    => $remote_data->sections->changelog,
            'installation' => $remote_data->sections->installation
        );
        
        return $res;
    }
}