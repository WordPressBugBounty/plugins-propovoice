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
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], '_ndpv_deactivate_nonce' ) ) {
            wp_send_json_error();
        }

        $reason_key = isset( $_POST['reason_key'] ) ? sanitize_text_field( $_POST['reason_key'] ) : '';
        $reason = isset( $_POST['reason'] ) ? sanitize_text_field( $_POST['reason'] ) : '';
        $data_collect = isset( $_POST['data_collect'] ) ? sanitize_text_field( $_POST['data_collect'] ) : '';

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
