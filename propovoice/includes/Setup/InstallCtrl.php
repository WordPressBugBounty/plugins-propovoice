<?php
namespace Ndpv\Setup;

use Ndpv\Setup\Type\Page;
use Ndpv\Setup\Type\Taxonomy;
use Ndpv\Setup\Type\TaxonomyService;
use Ndpv\Setup\Type\Update;

class InstallCtrl {


    public function __construct() {
        register_activation_hook( NDPV_FILE, [ $this, 'plugin_activate' ] );
        add_filter( 'cron_schedules', [ $this, 'custom_schedule' ] );
        register_activation_hook( NDPV_FILE, [ $this, 'schedule_my_cron' ] );

        add_action( 'admin_init', [ $this, 'insert_data' ] );
        add_action( 'admin_init', [ $this, 'plugin_redirect' ] );

        $this->pro_minimum_version_check();
    }

    public function custom_schedule( $schedules ) {
        $schedules['half_minute'] = [
            'interval' => 30,
            'display' => esc_html__( 'Every Half Minute', 'propovoice' ),
        ];

        $schedules['one_minute'] = [
            'interval' => 60,
            'display' => esc_html__( 'Every 1 Minute', 'propovoice' ),
        ];
        return $schedules;
    }

    public function schedule_my_cron() {

        //set cron job
        if ( ! wp_next_scheduled( 'ndpv_hourly_event' ) ) {
            wp_schedule_event( time(), 'hourly', 'ndpv_hourly_event' );
        }

        if ( ! wp_next_scheduled( 'ndpv_half_minute_event' ) ) {
            wp_schedule_event( time(), 'half_minute', 'ndpv_half_minute_event' );
        }

        if ( ! wp_next_scheduled( 'ndpv_one_minute_event' ) ) {
            wp_schedule_event( time(), 'one_minute', 'ndpv_one_minute_event' );
        }
    }

    public function plugin_activate() {
        add_option( 'ndpv_active', true );
    }

    public function plugin_redirect() {
        if ( get_option( 'ndpv_active', false ) ) {
            delete_option( 'ndpv_active' );

            wp_redirect( admin_url( 'admin.php?page=ndpv-welcome' ) );
        }
    }

    public function insert_data() {
        $version = get_option( 'ndpv_version', '0.1.0' );
        if ( version_compare( $version, NDPV_VERSION, '<' ) ) {
            update_option( 'ndpv_version', NDPV_VERSION );
        }

        if ( version_compare( $version, '1.0.0', '<' ) ) {
            new Page();
            new Taxonomy();
        }

        if ( version_compare( $version, '1.0.1.4', '<' ) ) {
            $term_id = wp_insert_term(
                'Unit', // the term
                'ndpv_estinv_qty_type', // the taxonomy
            );
            if ( ! is_wp_error( $term_id ) ) {
                update_term_meta( $term_id['term_id'], 'tax_pos', $term_id['term_id'] );
            }
        }

        if ( version_compare( $version, '1.7.5.1', '<' ) ) {
            new TaxonomyService();
        }

        new Update();
    }

    /**
     * Check minimum version of Propovoice pro
     *
     * @return void
     */
    public function pro_minimum_version_check() {
        // Get the list of active plugins for the current site
        $site_plugins = (array) get_option( 'active_plugins' );

        // Get the network-activated plugins
        $network_activated_plugins = (array) get_site_option( 'active_sitewide_plugins' );

        // Combine the lists of active plugins from the current site and network-activated plugins
        $all_active_plugins = array_unique( array_merge( $site_plugins, array_keys( $network_activated_plugins ) ) );

        if ( in_array( 'propovoice-pro/propovoice-pro.php', apply_filters( 'active_plugins', $all_active_plugins ), true ) ) {
            $version = get_option( 'ndpvp_version' );
            if ( version_compare( $version, '1.7.1.1', '<' ) ) {
                add_action(
                    'admin_notices', function () {

                        $class = 'notice notice-error';
                        $free_name = esc_html__( 'Propovoice', 'propovoice' );
                        $pro_name = esc_html__( 'Propovoice Pro', 'propovoice' );
                        $message = '';
                        $url = '';
                        $button_text = '';

                        $message = esc_html__( 'requires Propovoice Pro version minimum 1.7.1.1, Which is currently NOT RUNNING.', 'propovoice' );

                        $slug = 'propovoice-pro';
                        $url = wp_nonce_url(
                            admin_url( sprintf( 'update.php?action=upgrade-plugin&plugin=%1$s/%1$s.php', $slug ) ),
                            sprintf( 'upgrade-plugin_%1$s/%1$s.php', $slug )
                        );
                        $button_text = esc_html__( 'Update', 'propovoice' );

                        printf(
                            '
                <div class="%1$s">
                    <p><strong>%6$s</strong> %3$s</p>
                    <p><a class="button button-primary" href="%4$s">%5$s %2$s</a></p>
                </div>',
                            esc_html($class),
                            esc_html($pro_name),
                            esc_html($message),
                            esc_html($url),
                            esc_html($button_text),
                            esc_html($free_name)
                        );
                    }
                );
                return;
            }
        }
    }
}
