<?php
namespace Ndpv\Widget\Elementor;

use Elementor\Plugin;
use Ndpv\Widget\Elementor\Widgets\Registration;

class ElementorCtrl {

    public function __construct() {
		add_action( 'elementor/elements/categories_registered', [ $this, 'ndpv_category' ] );
        add_action( 'elementor/widgets/widgets_registered', [ $this, 'widgets_registered' ] );
    }

	/**
	 * @since 1.0
	 */
	public function widgets_registered() {
        // Plugin::instance()->widgets_manager->register_widget_type( new Registration() );
	}

	/**
	 * @since 1.0
	 */
	public function ndpv_category( $elements_manager ) {

		$elements_manager->add_category(
			'ndpv-category',
			[
				'title' => esc_html__( 'Propovoice Widgets', 'propovoice' ),
				'icon' => 'fa fa-plug',
			]
		);
	}
}
