<?php
namespace Ndpv\Assist\Type;

class Link {

    public function __construct() {
        add_filter( 'plugin_action_links_' . plugin_basename( NDPV_FILE ), [ $this, 'links' ] );
    }

	/**
     * Show necessary links with in plugin page
     *
     * @since 1.0.0
	 *
	 * @param array $links
	 *
	 * @return array
     */
	public function links( $links ) {
		$links[] = '<a target="_blank" href="' . esc_url( 'https://propovoice.com/docs' ) . '">Documentation</a>';
		if ( ! function_exists( 'ndpvp' ) ) {
			$links[] = '<a target="_blank" style="color: #39b54a;font-weight: 700;" href="' . esc_url( 'https://propovoice.com?utm_source=WordPress&utm_medium=propovoice&utm_campaign=pro_click' ) . '">Get Pro</a>';
		}

		if ( array_key_exists( 'deactivate', $links ) ) {
            $links['deactivate'] = str_replace( '<a', '<a class="ndpv-deactivate-link"', $links['deactivate'] );
        }
		return $links;
	}
}
