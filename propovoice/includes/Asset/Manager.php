<?php

namespace Ndpv\Asset;

use Ndpv\Helpers\Fns;
use Ndpv\Helpers\I18n;

/**
 * Asset Manager class.
 *
 * Responsible for managing all of the assets (CSS, JS, Images, Locales).
 *
 * @since 1.7.5
 */
class Manager {

    /**
     * Constructor.
     *
     * @since 1.7.5
     */
    public function __construct() {

        add_action( 'init', [ $this, 'register_all_scripts' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'public_scripts' ], 9999 );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ], 9999 );

        //remove thank you text from propovoice dashboard
        if ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'ndpv' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            add_filter( 'admin_footer_text', '__return_empty_string', 11 );
            add_filter( 'update_footer', '__return_empty_string', 11 );
        }

        add_filter( 'show_admin_bar', [ $this, 'hide_admin_bar' ] );

        add_action(
            'current_screen',
            function () {
                if ( ! $this->is_plugins_screen() ) {
                    return;
                }

                add_action(
                    'admin_enqueue_scripts',
                    [
                        $this,
                        'enqueue_feedback_dialog',
                    ]
                );
            }
        );
    }

    /**
     * Chunk translation
     *
     * @since 1.7.6.1
     *
     * @return void
     */
    private function chunks_translation()
    {
        $data = [
            'baseUrl' => '',
            'locale' => determine_locale(),
            'domainMap' => [],
            'domainPaths' => [],
        ];

        $lang_dir = WP_LANG_DIR;
        $content_dir = WP_CONTENT_DIR;
        $abspath = ABSPATH;

        if (strpos($lang_dir, $content_dir) === 0) {
            $data['baseUrl'] = esc_url(content_url(substr(trailingslashit($lang_dir), strlen(trailingslashit($content_dir)))));
        } elseif (strpos($lang_dir, $abspath) === 0) {
            $data['baseUrl'] = esc_url(site_url(substr(trailingslashit($lang_dir), strlen(untrailingslashit($abspath)))));
        }

        // Enqueue the script and localize the data
        wp_enqueue_script('propovoice-i18n-loader');
        $data['domainMap'] = (object) $data['domainMap'];
        $data['domainPaths'] = (object) $data['domainPaths'];
        wp_localize_script('propovoice-i18n-loader', 'propovoiceI18nState', $data);
    }

    /**
     * Register all scripts and styles.
     *
     * @since 1.7.5
     *
     * @return void
     */
    public function register_all_scripts() {
        $this->register_scripts($this->global_scripts());
        $this->chunks_translation();
        $this->register_styles( $this->get_styles() );
        $this->register_scripts( $this->get_scripts() );
    }

    /**
     * Get all styles.
     *
     * @since 1.7.5
     *
     * @return array
     */
    public function get_styles(): array {
        return [
            'ndpv-google-font' => [
                'src'     => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
                'version' => NDPV_VERSION,
                'deps'    => [],
            ],
            'ndpv-welcome' => [
                'src'     => NDPV_BUILD . '/welcome-style.css',
                'version' => NDPV_VERSION,
                'deps'    => [],
            ],
            'ndpv-style' => [
                'src'     => NDPV_BUILD . '/style.css',
                'version' => NDPV_VERSION,
                'deps'    => [],
            ],
            'ndpv-admin' => [
                'src'     => NDPV_BUILD . '/admin.css',
                'version' => NDPV_VERSION,
                'deps'    => [],
            ],
            'ndpv-est-inv' => [
                'src'     => NDPV_BUILD . '/est-inv.css',
                'version' => NDPV_VERSION,
                'deps'    => [],
            ],
            'ndpv-package' => [
                'src'     => NDPV_BUILD . '/package.css',
                'version' => NDPV_VERSION,
                'deps'    => [],
            ],
        ];
    }

    /**
     * Get all scripts.
     *
     * @since 1.7.6.1
     *
     * @return array
     */
    public function global_scripts(): array
    {
        $dependency = require_once NDPV_DIR . '/build/i18n-loader.asset.php';

        return [
            'propovoice-i18n-loader' => [
                'src'       => NDPV_BUILD . '/i18n-loader.js',
                'version'   => $dependency['version'],
                'deps'      => $dependency['dependencies'],
                'in_footer' => true
            ],
        ];
    }

    /**
     * Get all scripts.
     *
     * @since 1.7.5
     *
     * @return array
     */
    public function get_scripts(): array {
        $dependency_welcome = require_once NDPV_DIR . '/build/welcome.asset.php';
        $dependency_admin = require_once NDPV_DIR . '/build/admin.asset.php';
        $dependency_est_inv = require_once NDPV_DIR . '/build/est-inv.asset.php';
        $dependency_package = require_once NDPV_DIR . '/build/package.asset.php';
        $dependency_wp_plugin = require_once NDPV_DIR . '/build/wp-plugin.asset.php';

        return [
            'ndpv-welcome' => [
                'src'       => NDPV_BUILD . '/welcome.js',
                'version'   => $dependency_welcome['version'],
                'deps'      => $dependency_welcome['dependencies'],
                'in_footer' => true,
            ],
            'ndpv-admin' => [
                'src'       => NDPV_BUILD . '/admin.js',
                'version'   => $dependency_admin['version'],
                'deps'      => $dependency_admin['dependencies'],
                'in_footer' => true,
            ],
            'ndpv-est-inv' => [
                'src'       => NDPV_BUILD . '/est-inv.js',
                'version'   => $dependency_est_inv['version'],
                'deps'      => $dependency_est_inv['dependencies'],
                'in_footer' => true,
            ],
            'ndpv-package' => [
                'src'       => NDPV_BUILD . '/package.js',
                'version'   => $dependency_package['version'],
                'deps'      => $dependency_package['dependencies'],
                'in_footer' => true,
            ],
            'ndpv-plugin' => [
                'src'       => NDPV_BUILD . '/wp-plugin.js',
                'version'   => $dependency_wp_plugin['version'],
                'deps'      => $dependency_wp_plugin['dependencies'],
                'in_footer' => true,
            ],
        ];
    }

    /**
     * Register styles.
     *
     * @since 1.7.5
     *
     * @return void
     */
    public function register_styles( array $styles ) {
        foreach ( $styles as $handle => $style ) {
            wp_register_style( $handle, $style['src'], $style['deps'], $style['version'] );
        }
    }

    /**
     * Register scripts.
     *
     * @since 1.7.5
     *
     * @return void
     */
    public function register_scripts( array $scripts ) {
        foreach ( $scripts as $handle => $script ) {
            wp_register_script( $handle, $script['src'], $script['deps'], $script['version'], $script['in_footer'] );
        }
    }

    public function hide_admin_bar( $show ) {
        if (
            is_page_template(
                [
                    'workspace-template.php',
                    'invoice-template.php',
                    'estimate-template.php',
                    'package-template.php',
                    'form-template.php',
                ]
            )
        ) {
            return false;
        }
        return $show;
    }

    private function admin_public_script() {
        if (
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'ndpv-welcome' ) ||
            ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'ndpv' ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            is_page_template(
                [
                    'workspace-template.php',
                    'invoice-template.php',
                    'estimate-template.php',
                    'package-template.php',
                ]
            ) ||
            $this->is_plugins_screen()
        ) {
            wp_enqueue_style( 'ndpv-google-font' );
            wp_enqueue_style( 'ndpv-style' );
        }

        if ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'ndpv-welcome' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            wp_enqueue_style( 'ndpv-welcome' );
            wp_enqueue_script( 'ndpv-welcome' );
            wp_localize_script(
                'ndpv-welcome',
                'ndpv',
                [
                    'apiUrl' => esc_url( rest_url() ),
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'dashboard' => menu_page_url( 'ndpv', false ),
                    'assetImgUri' => ndpv()->get_asset_uri( 'img/' ),
                    'logo' => Fns::brand_logo(),
                    'i18n' => I18n::dashboard(),
                ]
            );
        }

        if ( is_page_template( [ 'invoice-template.php', 'estimate-template.php' ] ) ) {
            wp_enqueue_style( 'ndpv-est-inv' );
            wp_enqueue_script( 'ndpv-est-inv' );
        }

        if ( is_page_template( [ 'package-template.php' ] ) ) {
            wp_enqueue_script( 'ndpv-package' );
        }

        if (
            is_page_template( [ 'invoice-template.php', 'estimate-template.php', 'package-template.php' ] )
        ) {
            $local = [
                'apiUrl' => esc_url( rest_url() ),
                'assetUri' => trailingslashit( NDPV_URL ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'date_format' => Fns::phpToMomentFormat(
                    get_option( 'date_format' )
                ),
                'assetImgUri' => ndpv()->get_asset_uri( 'img/' ),
                'i18n' => I18n::dashboard(),
            ];
            wp_localize_script( 'ndpv-est-inv', 'ndpv', $local );
            wp_localize_script( 'ndpv-package', 'ndpv', $local );
        }

        if (
            ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'ndpv' ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            is_page_template(
                [
                    'workspace-template.php',
                    'invoice-template.php',
                    'estimate-template.php',
                    'package-template.php',
                ]
            )
        ) {
            wp_enqueue_style( 'ndpv-admin' );
        }
        if (
            ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'ndpv' ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            is_page_template(
                [
                    'workspace-template.php',
                ]
            )
        ) {
            wp_enqueue_script( 'ndpv-admin' );
            $current_user = wp_get_current_user();
            $segment = ( get_option( 'permalink_structure' ) === '' ? '&' : '?' ); //if plain permalink

            $current_user_caps = array_keys(
                array_filter( wp_get_current_user()->allcaps )
            );
            wp_localize_script(
                'ndpv-admin',
                'ndpv',
                [
                    'apiUrl' => esc_url( rest_url() ),
                    'siteUrl' => get_site_url(),
                    'version' => ndpv()->version(),
                    'dashboard' => admin_url( 'admin.php?page=ndpv' ),
                    'invoice_page_url' => sprintf(
                        '%s%sid=%s&token=%s',
                        Fns::client_page_url( 'invoice' ),
                        $segment,
                        'invoice_id',
                        'invoice_token'
                    ),
                    'estimate_page_url' => sprintf(
                        '%s%sid=%s&token=%s',
                        Fns::client_page_url( 'estimate' ),
                        $segment,
                        'invoice_id',
                        'invoice_token'
                    ),
                    'package_page_url' => sprintf(
                        '%s%sid=%s&token=%s',
                        Fns::client_page_url( 'package' ),
                        $segment,
                        'package_id',
                        'package_token'
                    ),
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'date_format' => Fns::phpToMomentFormat(
                        get_option( 'date_format' )
                    ),
                    'assetImgUri' => ndpv()->get_asset_uri( 'img/' ),
                    'logo' => Fns::brand_logo(),
                    'assetUri' => trailingslashit( NDPV_URL ),
                    'profile' => [
                        'id' => $current_user->ID,
                        'name' => $current_user->display_name,
                        'email' => $current_user->user_email,
                        'img' => get_avatar_url(
                            $current_user->ID,
                            [
                                'size' => '36',
                            ]
                        ),
                        'logout' => wp_logout_url( get_permalink() ),
                    ],
                    'i18n' => I18n::dashboard(),
                    'is_service_workflow' => ndpv()->is_active_module(),
                    'caps' => $current_user_caps,
                    'isDemo' => apply_filters( 'ndpv_demo', false ),
                    'demoMsg' => 'You are not allowed to change settings in demo mode!',
                ]
            );
        }
    }

    /**
     * Enqueue admin styles and scripts.
     *
     * @since 1.7.5
     *
     * @return void
     */
    public function admin_script() {
    }

    public function public_scripts() {
        $this->admin_public_script();

        $this->admin_script();
    }

    public function admin_scripts() {
        $this->admin_public_script();
        $this->admin_script();
    }

    /**
     * Enqueue feedback dialog scripts.
     *
     * Registers the feedback dialog scripts and enqueues them.
     *
     * @since 1.0.0
     * @access public
     */
    public function enqueue_feedback_dialog() {
        add_action( 'admin_footer', [ $this, 'deactivate_feedback_dialog' ] );
        wp_enqueue_script( 'ndpv-plugin' );
        wp_localize_script(
            'ndpv-plugin', 'ndpv', [
				'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
			]
        );
    }

    /**
     * @since 1.0.1.5
     */
    public function deactivate_feedback_dialog() {
        ndpv()->render( 'feedback/form' );
    }

    /**
     * @since 1.0.1.5
     */
    private function is_plugins_screen() {
        if ( ! function_exists( 'get_current_screen' ) ) {
            require_once ABSPATH . '/wp-admin/includes/screen.php';
        }

        if ( is_admin() ) {
            return in_array(
                get_current_screen()->id,
                [
                    'plugins',
                    'plugins-network',
                ]
            );
        } else {
            return false;
        }
    }
}
