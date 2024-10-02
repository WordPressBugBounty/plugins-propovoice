<?php
namespace Ndpv\Taxonomy;

class TaxonomyCtrl {

	public function __construct() {
		add_action( 'init', [ $this, 'register_taxonomy' ] );
	}

    public function register_taxonomy() {

		$taxonomies = [
            'lead_level' => 'lead',
            'lead_source' => 'lead',
            'deal_pipeline' => 'deal',
            'deal_stage' => 'deal',
            'tag' => 'deal',
            'task_status' => 'task',
            'task_type' => 'task',
            'task_priority' => 'task',
            'project_status' => 'project',
            'contact_status' => 'person',
            'extra_amount' => 'estinv',
            'estinv_qty_type' => 'estinv',
			'email_social' => 'setting',
			'package_status' => 'package',
			'order_status' => 'order',
			'request_status' => 'request',
        ];

		foreach ( $taxonomies as $tax => $post_type ) {
			if ( ! is_blog_installed() || taxonomy_exists( 'ndpv_' . $tax ) ) {
				return;
			}

			do_action( 'ndpv_' . $tax . '_taxonomy' );

			$args = [
				'hierarchical'      => true,
				'show_ui'           => false,
				'show_in_menu'      => false,
				'show_in_nav_menus' => false,
				'query_var'         => true,
				'rewrite'           => [ 'slug' => 'ndpv_' . $tax ],
			];

			register_taxonomy( 'ndpv_' . $tax, [ 'ndpv_' . $post_type ], apply_filters( 'ndpv_' . $tax . '_taxonomy_args', $args ) );

			do_action( 'ndpv_after_' . $tax . '_taxonomy' );
		}
    }
}
