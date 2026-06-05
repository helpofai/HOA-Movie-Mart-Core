<?php
/**
 * HOA Movie Mart Core — GitHub Plugin Updater
 *
 * Checks GitHub Releases for new versions of the core plugin.
 * Since the plugin lives in the same repo as the theme, both
 * update together when a new release is published.
 *
 * @package HOA_Movie_Mart_Core
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class HOA_Plugin_Updater {

    private $github_owner = 'helpofai';
    private $github_repo  = 'hoa-movie-mart-core';
    private $plugin_slug;
    private $plugin_file;
    private $current_version;
    private $api_url;
    private $cache_key;
    private $cache_ttl = 12 * HOUR_IN_SECONDS;

    public function __construct( $plugin_file ) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename( $plugin_file );

        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $data = get_plugin_data( $plugin_file );
        $this->current_version = $data['Version'];

        $this->api_url   = "https://api.github.com/repos/{$this->github_owner}/{$this->github_repo}/releases/latest";
        $this->cache_key = 'hoa_plugin_update_' . md5( $this->api_url );

        // Optional token from theme settings (shared)
        $options = get_option( 'hoa_movie_mart_settings' );

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
        add_filter( 'plugins_api',                           array( $this, 'plugin_info' ), 20, 3 );
        add_filter( 'upgrader_package_options',               array( $this, 'maybe_clear_cache' ) );
    }

    // ——— Check for updates ———

    public function check_for_update( $transient ) {
        if ( ! is_object( $transient ) ) {
            $transient = new stdClass();
        }

        $release = $this->fetch_release();
        if ( ! $release ) return $transient;

        $remote_ver = ltrim( $release['tag_name'], 'vV' );
        if ( ! version_compare( $remote_ver, $this->current_version, '>' ) ) {
            return $transient;
        }

        $transient->response[ $this->plugin_slug ] = (object) array(
            'slug'        => dirname( $this->plugin_slug ),
            'plugin'      => $this->plugin_slug,
            'new_version' => $remote_ver,
            'url'         => $release['html_url'],
            'package'     => $release['zipball_url'],
            'tested'      => '6.7',
            'requires_php'=> '7.2',
        );

        return $transient;
    }

    // ——— Plugin info popup ———

    public function plugin_info( $result, $action, $args ) {
        if ( 'plugin_information' !== $action ) return $result;
        if ( dirname( $this->plugin_slug ) !== $args->slug ) return $result;

        $release = $this->fetch_release();
        if ( ! $release ) return $result;

        $remote_ver = ltrim( $release['tag_name'], 'vV' );

        return (object) array(
            'name'          => 'HOA Movie Mart Core',
            'slug'          => dirname( $this->plugin_slug ),
            'version'       => $remote_ver,
            'author'        => '<a href="https://helpofai.com">HelpOfAi Team</a>',
            'homepage'      => 'https://helpofai.com',
            'requires'      => '5.0',
            'tested'        => '6.7',
            'requires_php'  => '7.2',
            'last_updated'  => $release['published_at'],
            'sections'      => array(
                'description'  => 'Core functionality for HOA Movie Mart theme — Movie CPT, taxonomies, meta boxes, and widgets.',
                'changelog'    => '<pre>' . esc_html( $release['body'] ) . '</pre>',
            ),
            'download_link' => $release['zipball_url'],
        );
    }

    // ——— Fetch from GitHub ———

    private function fetch_release() {
        $cached = get_transient( $this->cache_key );
        if ( false !== $cached ) return $cached;

        $options = get_option( 'hoa_movie_mart_settings' );

        $args = array(
            'timeout'   => 15,
            'headers'   => array(
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'HOA-Plugin-Updater/' . $this->current_version,
            ),
            'sslverify' => true,
        );

        if ( ! empty( $options['github_token'] ) ) {
            $args['headers']['Authorization'] = 'token ' . $options['github_token'];
        }

        $response = wp_remote_get( $this->api_url, $args );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            set_transient( $this->cache_key, null, HOUR_IN_SECONDS );
            return null;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! $body || empty( $body['tag_name'] ) ) {
            set_transient( $this->cache_key, null, HOUR_IN_SECONDS );
            return null;
        }

        $release = array(
            'tag_name'    => $body['tag_name'],
            'html_url'    => $body['html_url'],
            'zipball_url' => $body['zipball_url'],
            'published_at'=> $body['published_at'],
            'body'         => $body['body'],
        );

        set_transient( $this->cache_key, $release, $this->cache_ttl );
        return $release;
    }

    public function maybe_clear_cache( $options ) {
        if ( isset( $options['hook_extra']['plugin'] ) && $this->plugin_slug === $options['hook_extra']['plugin'] ) {
            delete_transient( $this->cache_key );
        }
        return $options;
    }
}
