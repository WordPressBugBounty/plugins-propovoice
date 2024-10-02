<?php
namespace Ndpv\Hook\Type;

class Filter {

    public function __construct() {
        add_filter( 'body_class', [ $this, 'body_class' ] );
        add_filter( 'admin_body_class', [ $this, 'admin_body_class' ] );
        add_filter(
            'ajax_query_attachments_args', [
				$this,
				'hide_bank_attachment',
			]
        );

        add_filter( 'woocommerce_prevent_admin_access', '__return_false' );
    }

    public function body_class( $classes ) {
        if (
            is_page_template(
                [
					'workspace-template.php',
					'invoice-template.php',
					'estimate-template.php',
					'package-template.php',
				]
            )
        ) {
            $classes[] = 'ndpv';
            $classes[] = get_option( 'template' ) . '-theme';
        }

        if ( current_user_can( 'ndpv_client_role' ) ) {
            $classes[] = 'ndpv-client';
        }
        return $classes;
    }

    function admin_body_class( $classes ) {
        if (
            ( isset( $_GET['page'] ) && $_GET['page'] === 'ndpv' ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            ( isset( $_GET['page'] ) && $_GET['page'] === 'ndpv-welcome' ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        ) {
            $classes .= ' ndpv ' . get_option( 'template' ) . '-theme';
        }

        return $classes;
    }

    /**
     * Hide attachment files from the Media Library's overlay (modal) view
     * if they have a certain meta key and value set.
     *
     * @param array $args An array of query variables.
     */
    public function hide_bank_attachment( $args ) {

        if ( ! is_admin() ) {
            return;
        }

        // Modify the query.
        $args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            'relation' => 'OR',
            [
                'key' => 'ndpv_attach_type',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key' => 'ndpv_attach_type',
                'value' => 'secret',
                'compare' => '!=',
            ],
        ];

        return $args;
    }
}
