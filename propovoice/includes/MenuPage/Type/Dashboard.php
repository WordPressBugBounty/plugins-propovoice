<?php

namespace Ndpv\MenuPage\Type;

use Ndpv\Helpers\Fns;

class Dashboard {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu' ], 30 );
	}

	public function add_menu() {

		if ( ! current_user_can( 'ndpv_core' ) ) {
			return;
        }

		if ( ! is_multisite() ) {
			if ( current_user_can( 'ndpv_client_role' ) ) {
				return; // not allowed for client
			}
		}

		add_menu_page(
			esc_html__( 'Propovoice', 'propovoice' ),
			esc_html__( 'Propovoice', 'propovoice' ),
			'ndpv_core',
			'ndpv',
			[ $this, 'render' ],
			'dashicons-groups',
			2
		);

		add_submenu_page(
			'ndpv',
			esc_html__( 'Dashboard', 'propovoice' ),
			esc_html__( 'Dashboard', 'propovoice' ),
			'ndpv_dashboard',
			'ndpv#',
			[ $this, 'render' ]
		);

		$settings_menu = [
			[
                'id' => 'lead',
                'label' => esc_html__( 'Lead', 'propovoice' ),
                'capability' => 'ndpv_lead',
            ],
            [
                'id' => 'deal',
                'label' => esc_html__( 'Deal Pipeline', 'propovoice' ),
                'capability' => 'ndpv_deal',
            ],
            [
                'id' => 'client',
                'label' => esc_html__( 'Client', 'propovoice' ),
                'capability' => 'ndpv_client',
            ],
            [
                'id' => 'project',
                'label' => esc_html__( 'Project', 'propovoice' ),
                'capability' => 'ndpv_project',
            ],
            [
                'id' => 'estimate',
                'label' => esc_html__( 'Estimate', 'propovoice' ),
                'capability' => 'ndpv_estimate',
            ],
            [
                'id' => 'invoice',
                'label' => esc_html__( 'Invoice', 'propovoice' ),
                'capability' => 'ndpv_invoice',
            ],
            [
                'id' => 'contact',
                'label' => esc_html__( 'Contact', 'propovoice' ),
                'capability' => 'ndpv_contact',
            ],
		];

		if ( ndpv()->wage() ) {
			array_push(
				$settings_menu,
				[
					'id'         => 'workflow',
					'label'      => esc_html__( 'Workflow', 'propovoice' ),
					'capability' => 'ndpv_workflow',
				]
			);
		}

		$settings_menu = apply_filters( 'ndpv_sidebar_menu', $settings_menu );

		foreach ( $settings_menu as $menu ) {
			$menu_id = $menu['id'];

			add_submenu_page(
				'ndpv',
				$menu['label'],
				$menu['label'],
				$menu['capability'],
				'ndpv#/' . $menu_id,
				[ $this, 'render' ]
			);
		}

		add_submenu_page(
			'ndpv',
			esc_html__( 'Settings', 'propovoice' ),
			esc_html__( 'Settings', 'propovoice' ),
			'ndpv_setting',
			'ndpv#/setting/general',
			[ $this, 'render' ]
		);

		if ( ndpv()->wage() ) {
			global $submenu;
			$permalink = Fns::client_page_url( 'workspace' );
			if ( $permalink ) {
				$submenu['ndpv'][] = [
					esc_html__( 'Go to Frontend', 'propovoice' ),
					'ndpv_workspace',
					$permalink,
				];
			}
		}

		if ( ! function_exists( 'ndpvp' ) ) {
			global $submenu;
			$submenu['ndpv'][] = [
				'Upgrade to Pro',
				'ndpv_core',
				'https://propovoice.com',
			];
		}

		remove_submenu_page( 'ndpv', 'ndpv' );
	}

	public function render() {
		echo '<div class="wrap"><div id="ndpv-dashboard"></div></div>';
	}
}
