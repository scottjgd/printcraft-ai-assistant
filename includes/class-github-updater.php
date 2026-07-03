<?php
/**
 * GitHub Updater — hooks into WordPress's native update system
 * to check your GitHub repository for new plugin releases.
 *
 * How it works:
 *  1. Once daily, WordPress pings GitHub's API for the latest release.
 *  2. If the release tag version is newer than the installed version,
 *     WordPress shows "Update Available" in Plugins > Installed Plugins.
 *  3. Clicking "Update" downloads the release ZIP straight from GitHub.
 *
 * Requirements on your GitHub repo:
 *  - Each release must be tagged with the version number, e.g. v1.2.0 or 1.2.0
 *  - The release must include a ZIP asset named printcraft-ai-assistant.zip
 *    OR the auto-generated source ZIP from GitHub will be used.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class PCAI_GitHub_Updater {

    private $plugin_slug;
    private $plugin_file;
    private $github_user;
    private $github_repo;
    private $current_version;
    private $transient_key;

    public function __construct() {
        $this->plugin_file     = PCAI_PLUGIN_FILE;
        $this->plugin_slug     = plugin_basename( PCAI_PLUGIN_FILE );
        $this->github_user     = get_option( 'pcai_github_user', '' );
        $this->github_repo     = get_option( 'pcai_github_repo', '' );
        $this->current_version = PCAI_VERSION;
        $this->transient_key   = 'pcai_github_release_cache';
    }

    public function init() {
        if ( empty( $this->github_user ) || empty( $this->github_repo ) ) {
            return;
        }

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
        add_filter( 'upgrader_post_install', array( $this, 'post_install' ), 10, 3 );
        add_filter( 'upgrader_source_selection', array( $this, 'rename_source' ), 10, 4 );
    }

    private function get_latest_release() {
        $cached = get_transient( $this->transient_key );
        if ( $cached !== false ) return $cached;

        $url      = "https://api.github.com/repos/{$this->github_user}/{$this->github_repo}/releases/latest";
        $token    = get_option( 'pcai_github_token', '' );
        $headers  = array(
            'Accept'     => 'application/vnd.github+json',
            'User-Agent' => 'PrintCraft-AI-WP-Updater/' . $this->current_version,
        );
        if ( $token ) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $response = wp_remote_get( $url, array(
            'timeout' => 15,
            'headers' => $headers,
        ) );

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            set_transient( $this->transient_key, null, 3600 );
            return null;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        set_transient( $this->transient_key, $data, 43200 ); // 12 hours
        return $data;
    }

    public function check_for_update( $transient ) {
        if ( empty( $transient->checked ) ) return $transient;

        $release = $this->get_latest_release();
        if ( ! $release ) return $transient;

        $remote_version = ltrim( $release['tag_name'], 'vV' );

        if ( version_compare( $this->current_version, $remote_version, '<' ) ) {
            $download_url = $this->get_download_url( $release );

            $transient->response[ $this->plugin_slug ] = (object) array(
                'slug'        => dirname( $this->plugin_slug ),
                'plugin'      => $this->plugin_slug,
                'new_version' => $remote_version,
                'url'         => "https://github.com/{$this->github_user}/{$this->github_repo}",
                'package'     => $download_url,
                'tested'      => get_bloginfo( 'version' ),
                'icons'       => array(),
                'banners'     => array(),
                'requires_php'=> '7.4',
            );
        }

        return $transient;
    }

    public function plugin_info( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) return $result;
        if ( ! isset( $args->slug ) || $args->slug !== dirname( $this->plugin_slug ) ) return $result;

        $release = $this->get_latest_release();
        if ( ! $release ) return $result;

        $remote_version = ltrim( $release['tag_name'], 'vV' );

        return (object) array(
            'name'          => 'PrintCraft AI Assistant',
            'slug'          => dirname( $this->plugin_slug ),
            'version'       => $remote_version,
            'author'        => '<a href="https://printcraftcreations.ca">Print Craft Creations</a>',
            'homepage'      => "https://github.com/{$this->github_user}/{$this->github_repo}",
            'download_link' => $this->get_download_url( $release ),
            'sections'      => array(
                'description' => 'AI-powered customer service chatbot for Print Craft Creations.',
                'changelog'   => isset( $release['body'] ) ? nl2br( esc_html( $release['body'] ) ) : 'See GitHub for changelog.',
            ),
            'requires'      => '5.8',
            'tested'        => get_bloginfo( 'version' ),
            'requires_php'  => '7.4',
            'last_updated'  => isset( $release['published_at'] ) ? date( 'Y-m-d', strtotime( $release['published_at'] ) ) : '',
        );
    }

    private function get_download_url( $release ) {
        if ( ! empty( $release['assets'] ) ) {
            foreach ( $release['assets'] as $asset ) {
                if ( isset( $asset['name'] ) && $asset['name'] === 'printcraft-ai-assistant.zip' ) {
                    return $asset['browser_download_url'];
                }
            }
        }
        // Fall back to the auto-generated source ZIP
        return "https://github.com/{$this->github_user}/{$this->github_repo}/archive/refs/tags/{$release['tag_name']}.zip";
    }

    public function rename_source( $source, $remote_source, $upgrader, $hook_extra = null ) {
        if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_slug ) {
            return $source;
        }

        global $wp_filesystem;
        $correct_dir = trailingslashit( $remote_source ) . 'printcraft-ai-assistant/';

        if ( $wp_filesystem->is_dir( $source ) && basename( untrailingslashit( $source ) ) !== 'printcraft-ai-assistant' ) {
            $wp_filesystem->move( $source, $correct_dir );
            return $correct_dir;
        }

        return $source;
    }

    public function post_install( $response, $hook_extra, $result ) {
        if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_slug ) {
            return $response;
        }
        activate_plugin( $this->plugin_slug );
        return $result;
    }

    public static function clear_cache() {
        delete_transient( 'pcai_github_release_cache' );
    }
}
