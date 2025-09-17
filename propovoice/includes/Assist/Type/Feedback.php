<?php
namespace Ndpv\Assist\Type;

use Ndpv\Helpers\Info;

class Feedback {

    private $api = 'https://propovoice.com/wp-json/ndpva/v1/';

	public function __construct() {
		add_action( 'wp_ajax_ndpv_deactivate_feedback', [ $this, 'deactivate' ] );
	}


    /**
     * When deactivate this plugin get feedback
     *
     * @since 1.0.0
     */
    public function deactivate() {
// Unsplash and sanitize POST inputs
$nonce        = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
$reason_key   = isset( $_POST['reason_key'] ) ? sanitize_text_field( wp_unslash( $_POST['reason_key'] ) ) : '';
$reason       = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
$data_collect = isset( $_POST['data_collect'] ) ? sanitize_text_field( wp_unslash( $_POST['data_collect'] ) ) : '';

// Verify nonce
if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, '_ndpv_deactivate_nonce' ) ) {
    wp_send_json_error();
    wp_die(); // optional: terminate immediately
}


        $data = [];
        if ( $data_collect ) {
            $info = new Info();
            $data = $info->name_email();
        }
        $data['reason_key'] = $reason_key;
        $data['reason'] = $reason;
        $data['version'] = NDPV_VERSION;
        $data['package'] = 'free';

        wp_remote_post(
            $this->api . 'uninstaller', [
				'timeout' => 0.01,
				'body' => $data,
				'blocking'  => false,
				'sslverify'   => false,
			]
        );

        wp_send_json_success();
    }
}
