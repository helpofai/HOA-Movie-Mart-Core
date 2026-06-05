<?php
/**
 * HOA Movie Mart Core — GitHub Plugin Updater (Commit-based)
 *
 * Monitors latest commit on the plugin repo. No releases needed.
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
    private $commit_api_url;
    private $zipball_url;
    private $cache_key;
    private $cache_ttl = 6 * HOUR_IN_SECONDS;

    public function __construct( $plugin_file ) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename( $plugin_file );

        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $data = get_plugin_data( $plugin_file );
        $this->current_version = $data['Version'];

        $this->commit_api_url = "https://api.github.com/repos/{$this->github_owner}/{$this->github_repo}/commits?per_page=1";
        $this->zipball_url    = "https://api.github.com/repos/{$this->github_owner}/{$this->github_repo}/zipball";
        $this->cache_key      = 'hoa_plugin_update_' . md5( $this->commit_api_url );

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
        add_filter( 'plugins_api',                           array( $this, 'plugin_info' ), 20, 3 );
        add_filter( 'upgrader_package_options',               array( $this, 'maybe_clear_cache' ) );
        add_filter( 'upgrader_source_selection',              array( $this, 'fix_github_folder' ), 10, 4 );
    }

    public function check_for_update( $transient ) {
        if ( ! is_object( $transient ) ) {
            $transient = new stdClass();
        }

        $commit = $this->fetch_latest_commit();
        if ( ! $commit ) return $transient;

        $stored_sha = get_option( 'hoa_plugin_last_sha', '' );
        if ( $stored_sha === $commit['sha'] ) return $transient;

        if ( empty( $stored_sha ) ) {
            update_option( 'hoa_plugin_last_sha', $commit['sha'] );
            return $transient;
        }

        $new_ver = $commit['version'] ?: $commit['short_sha'];
        $transient->response[ $this->plugin_slug ] = (object) array(
            'slug'        => dirname( $this->plugin_slug ),
            'plugin'      => $this->plugin_slug,
            'new_version' => $new_ver,
            'url'         => $commit['html_url'],
            'package'     => $this->zipball_url,
            'tested'      => '6.7',
            'requires_php'=> '7.2',
        );

        return $transient;
    }

    public function plugin_info( $result, $action, $args ) {
        if ( 'plugin_information' !== $action ) return $result;
        if ( dirname( $this->plugin_slug ) !== $args->slug ) return $result;

        $commit = $this->fetch_latest_commit();
        if ( ! $commit ) return $result;

        return (object) array(
            'name'          => 'HOA Movie Mart Core',
            'slug'          => dirname( $this->plugin_slug ),
            'version'       => $commit['short_sha'],
            'author'        => '<a href="https://helpofai.com">HelpOfAi Team</a>',
            'homepage'      => 'https://helpofai.com',
            'requires'      => '5.0',
            'tested'        => '6.7',
            'requires_php'  => '7.2',
            'last_updated'  => $commit['date'],
            'sections'      => array(
                'description'  => 'Core functionality for HOA Movie Mart theme.',
                'changelog'    => '<pre>' . esc_html( $commit['message'] ) . '</pre>',
            ),
            'download_link' => $this->zipball_url,
        );
    }

    private function fetch_latest_commit() {
        $cached = get_transient( $this->cache_key );
        if ( false !== $cached ) return $cached;

        $options = get_option( 'hoa_movie_mart_settings' );
        $token   = ! empty( $options['github_token'] ) ? $options['github_token'] : null;

        $args = array(
            'timeout'   => 15,
            'headers'   => array(
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'HOA-Plugin-Updater/' . $this->current_version,
            ),
            'sslverify' => ! $this->is_local(),
        );

        if ( $token ) {
            $args['headers']['Authorization'] = 'token ' . $token;
        }

        $response = wp_remote_get( $this->commit_api_url, $args );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            set_transient( $this->cache_key, null, HOUR_IN_SECONDS );
            return null;
        }

        $commits = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $commits ) || ! is_array( $commits ) ) {
            set_transient( $this->cache_key, null, HOUR_IN_SECONDS );
            return null;
        }

        $c = $commits[0];
        $full_sha = $c['sha'];
        $remote_ver = null;
        $raw_url = "https://raw.githubusercontent.com/{$this->github_owner}/{$this->github_repo}/{$full_sha}/hoa-movie-mart-core.php";
        $raw_resp = wp_remote_get( $raw_url, array( 'timeout' => 10, 'sslverify' => ! $this->is_local(), 'headers' => array( 'User-Agent' => 'HOA-Plugin-Updater' ) ) );
        if ( ! is_wp_error( $raw_resp ) && 200 === wp_remote_retrieve_response_code( $raw_resp ) ) {
            if ( preg_match( '/Version:\s*([0-9.]+)/i', wp_remote_retrieve_body( $raw_resp ), $m ) ) {
                $remote_ver = $m[1];
            }
        }
        $result = array(
            'sha'        => $full_sha,
            'short_sha'  => substr( $full_sha, 0, 7 ),
            'message'    => $c['commit']['message'],
            'author'     => $c['commit']['author']['name'],
            'date'       => $c['commit']['author']['date'],
            'html_url'   => $c['html_url'],
            'version'    => $remote_ver,
        );

        set_transient( $this->cache_key, $result, $this->cache_ttl );
        return $result;
    }

    public function maybe_clear_cache( $options ) {
        if ( isset( $options['hook_extra']['plugin'] ) && $this->plugin_slug === $options['hook_extra']['plugin'] ) {
            delete_transient( $this->cache_key );
            $commit = $this->fetch_latest_commit();
            if ( $commit ) {
                update_option( 'hoa_plugin_last_sha', $commit['sha'] );
            }
        }
        return $options;
    }

    public function fix_github_folder( $source, $remote_source, $upgrader, $extra ) {
        $plugin_dir = 'hoa-movie-mart-core';
        if ( isset( $extra['plugin'] ) && dirname( $extra['plugin'] ) === $plugin_dir ) {
            if ( is_dir( $source ) && basename( $source ) !== $plugin_dir ) {
                $new_source = trailingslashit( dirname( $source ) ) . $plugin_dir;
                if ( $source !== $new_source ) {
                    if ( is_dir( $new_source ) ) {
                        $GLOBALS['wp_filesystem']->delete( $new_source, true );
                    }
                    rename( $source, $new_source );
                    $source = $new_source;
                }
            }
        }
        return $source;
    }

    private function is_local() {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        return in_array( $host, array( 'localhost', '127.0.0.1', '::1' ) )
            || substr( $host, -6 ) === '.local'
            || substr( $host, -7 ) === '.test';
    }
}
