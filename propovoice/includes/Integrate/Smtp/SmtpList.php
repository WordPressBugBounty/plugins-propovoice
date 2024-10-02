<?php
namespace Ndpv\Integrate\Smtp;

class SmtpList {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'rest_routes' ] );
    }

    public function rest_routes() {
        register_rest_route(
            'ndpv/v1', '/intg-smtp', [
				'methods' => 'GET',
				'callback' => [ $this, 'get' ],
				'permission_callback' => [ $this, 'get_per' ],
			]
        );
    }

    public function get( $req ) {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $list = [
            [
                'name' => 'Default',
                'slug' => null,
                'img' => 'https://cdn.cdnlogo.com/logos/p/71/php.svg',
                'active' => false,
                'pro' => false,
            ],
            [
                'name' => 'Other SMTP',
                'slug' => 'other',
                'img' => 'https://cdn.cdnlogo.com/logos/m/46/mail-ios.svg',
                'active' => false,
                'pro' => true,
            ]
        ];

        $form_list = [];
        $smtp = get_option( 'ndpv_smtp' );
        foreach ( $list as $value ) {
            if ( $value['slug'] == $smtp ) { //=== not working
                $value['active'] = true;
            }
            $form_list[] = $value;
        }

        wp_send_json_success( $form_list );
    }

    public function get_per() {
        return current_user_can( 'ndpv_setting' );
    }
}
