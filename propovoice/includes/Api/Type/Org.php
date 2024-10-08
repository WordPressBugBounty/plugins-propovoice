<?php

namespace Ndpv\Api\Type;

use Ndpv\Models\Client;
use Ndpv\Models\Person;
use Ndpv\Traits\Singleton;

class Org {

    use Singleton;

    public function routes() {
        register_rest_route(
            'ndpv/v1', '/organizations/(?P<id>\d+)', [
				'methods' => 'GET',
				'callback' => [ $this, 'get_single' ],
				'permission_callback' => [ $this, 'get_per' ],
				'args' => [
					'id' => [
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					],
				],
			]
        );

        register_rest_route(
            'ndpv/v1', '/organizations' . ndpv()->plain_route(), [
				'methods' => 'GET',
				'callback' => [ $this, 'get' ],
				'permission_callback' => [ $this, 'get_per' ],
			]
        );

        register_rest_route(
            'ndpv/v1', '/organizations', [
				'methods' => 'POST',
				'callback' => [ $this, 'create' ],
				'permission_callback' => [ $this, 'create_per' ],
			]
        );

        register_rest_route(
            'ndpv/v1', '/organizations/(?P<id>\d+)', [
				'methods' => 'PUT',
				'callback' => [ $this, 'update' ],
				'permission_callback' => [ $this, 'update_per' ],
				'args' => [
					'id' => [
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
					],
				],
			]
        );

        register_rest_route(
            'ndpv/v1', '/organizations/(?P<id>[0-9,]+)', [
				'methods' => 'DELETE',
				'callback' => [ $this, 'delete' ],
				'permission_callback' => [ $this, 'del_per' ],
				'args' => [
					'id' => [
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
        );
    }

    public function get( $req ) {
        $param = $req->get_params();

        $per_page = 10;
        $offset = 0;

        $s = isset( $param['text'] ) ? sanitize_text_field( $param['text'] ) : '';

        //for searching contact from other module
        $name = isset( $param['name'] ) ? sanitize_text_field( $param['name'] ) : '';
        if ( $name ) {
            $s = $name;
        }

        if ( isset( $param['per_page'] ) ) {
            $per_page = $param['per_page'];
        }

        if ( isset( $param['page'] ) && $param['page'] > 1 ) {
            $offset = $per_page * $param['page'] - $per_page;
        }

        $args = [
            'post_type' => 'ndpv_org',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'offset' => $offset,
        ];

        $args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            'relation' => 'OR',
        ];

        if ( $s ) {
            $args['meta_query'][] = [
                [
                    'key' => 'name',
                    'value' => $s,
                    'compare' => 'Like',
                ],
            ];
            $args['meta_query'][] = [
                [
                    'key' => 'email',
                    'value' => $s,
                    'compare' => 'Like',
                ],
            ];
        }

        $query = new \WP_Query( $args );
        $total_data = $query->found_posts; //use this for pagination
        $result = $data = [];
        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();

            $query_data = [];
            $query_data['id'] = $id;

            $query_meta = get_post_meta( $id );
            $query_data['name'] = isset( $query_meta['name'] )
                ? $query_meta['name'][0]
                : '';
            $query_data['person_id'] = isset( $query_meta['person_id'] )
                ? $query_meta['person_id'][0]
                : '';
            $query_data['first_name'] = isset( $query_meta['person_id'] ) && $query_meta['person_id']
                ? get_post_meta( $query_data['person_id'], 'first_name', true )
                : '';
            $query_data['email'] = isset( $query_meta['email'] )
                ? $query_meta['email'][0]
                : '';
            $query_data['web'] = isset( $query_meta['web'] )
                ? $query_meta['web'][0]
                : '';
            $query_data['mobile'] = isset( $query_meta['mobile'] )
                ? $query_meta['mobile'][0]
                : '';
            $query_data['country'] = isset( $query_meta['country'] )
                ? $query_meta['country'][0]
                : '';
            $query_data['region'] = isset( $query_meta['region'] )
                ? $query_meta['region'][0]
                : '';
            $query_data['address'] = isset( $query_meta['address'] )
                ? $query_meta['address'][0]
                : '';

            $logo_id = isset( $query_meta['logo'] ) ? $query_meta['logo'][0] : null;
            $logo_data = null;
            if ( $logo_id ) {
                $logo_src = wp_get_attachment_image_src( $logo_id, 'thumbnail' );
                if ( $logo_src ) {
                    $logo_data = [];
                    $logo_data['id'] = $logo_id;
                    $logo_data['src'] = $logo_src[0];
                }
            }
            $query_data['logo'] = $logo_data;

            $query_data['date'] = get_the_time( get_option( 'date_format' ) );
            $data[] = $query_data;
        }
        wp_reset_postdata();

        $result['result'] = $data;
        $result['total'] = $total_data;

        wp_send_json_success( $result );
    }

    public function get_single( $req ) {
        $url_params = $req->get_url_params();
        $id = $url_params['id'];
        $query_data = [];
        $query_data['id'] = $id;

        $org_meta = get_post_meta( $id );
        $query_data['name'] = isset( $org_meta['name'] )
            ? $org_meta['name'][0]
            : '';
        $query_data['email'] = isset( $org_meta['email'] )
            ? $org_meta['email'][0]
            : '';
        $query_data['web'] = isset( $org_meta['web'] )
            ? $org_meta['web'][0]
            : '';
        $query_data['mobile'] = isset( $org_meta['mobile'] )
            ? $org_meta['mobile'][0]
            : '';
        $query_data['country'] = isset( $org_meta['country'] )
            ? $org_meta['country'][0]
            : '';
        $query_data['region'] = isset( $org_meta['region'] )
            ? $org_meta['region'][0]
            : '';
        $query_data['address'] = isset( $org_meta['address'] )
            ? $org_meta['address'][0]
            : '';

        $query_data['client_portal'] = isset( $org_meta['client_portal'] )
            ? $org_meta['client_portal'][0]
            : false;

        $logo_id = isset( $org_meta['logo'] ) ? $org_meta['logo'][0] : null;
        $logo_data = null;
        if ( $logo_id ) {
            $logo_src = wp_get_attachment_image_src( $logo_id, 'thumbnail' );
            if ( $logo_src ) {
                $logo_data = [];
                $logo_data['id'] = $logo_id;
                $logo_data['src'] = $logo_src[0];
            }
        }
        $query_data['logo'] = $logo_data;
        $data = [];
        $data['profile'] = $query_data;

        wp_send_json_success( $data );
    }

    public function create( $req ) {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $name = isset( $param['name'] )
            ? sanitize_text_field( $req['name'] )
            : null;
        $first_name = isset( $param['first_name'] )
            ? sanitize_text_field( $req['first_name'] )
            : null;
        $person_id = isset( $param['person_id'] )
            ? absint( $param['person_id'] )
            : null;
        $email = isset( $param['email'] )
            ? strtolower( sanitize_email( $req['email'] ) )
            : null;
        $web = isset( $param['web'] ) ? esc_url_raw( $req['web'] ) : null;
        $mobile = isset( $param['mobile'] )
            ? sanitize_text_field( $req['mobile'] )
            : null;
        $country = isset( $param['country'] )
            ? sanitize_text_field( $req['country'] )
            : null;
        $region = isset( $param['region'] )
            ? sanitize_text_field( $req['region'] )
            : null;
        $address = isset( $param['address'] )
            ? sanitize_text_field( $req['address'] )
            : null;
        $logo = isset( $param['logo'] ) ? absint( $param['logo'] ) : null;

        if ( empty( $name ) ) {
            $reg_errors->add(
                'field',
                esc_html__( 'Name field is missing', 'propovoice' )
            );
        }

        if ( ! is_email( $email ) ) {
            $reg_errors->add(
                'email_invalid',
                esc_html__( 'Email id is not valid!', 'propovoice' )
            );
        }

        if ( $reg_errors->get_error_messages() ) {
            wp_send_json_error( $reg_errors->get_error_messages() );
        } else {
            $data = [
                'post_type' => 'ndpv_org',
                'post_title' => $name,
                'post_content' => '',
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
            ];
            $post_id = wp_insert_post( $data );

            if ( ! is_wp_error( $post_id ) ) {
                update_post_meta( $post_id, 'ws_id', ndpv()->get_workspace() );

                if ( $name ) {
                    update_post_meta( $post_id, 'name', $name );
                }

                if ( ! $person_id && $first_name ) {
                    $person = new Person();
                    $person_id = $person->create(
                        [
							'first_name' => $first_name,
							'org_id' => $post_id,
						]
                    );
                }

                if ( $person_id ) {
                    update_post_meta( $post_id, 'person_id', $person_id );
                }

                if ( $email ) {
                    update_post_meta( $post_id, 'email', $email );
                }

                if ( $web ) {
                    update_post_meta( $post_id, 'web', $web );
                }

                if ( $mobile ) {
                    update_post_meta( $post_id, 'mobile', $mobile );
                }

                if ( $country ) {
                    update_post_meta( $post_id, 'country', $country );
                }

                if ( $region ) {
                    update_post_meta( $post_id, 'region', $region );
                }

                if ( $address ) {
                    update_post_meta( $post_id, 'address', $address );
                }

                if ( $logo ) {
                    update_post_meta( $post_id, 'logo', $logo );
                }

                do_action( 'ndpvp_webhook', 'contact_add', $param );

                wp_send_json_success( $post_id );
            } else {
                wp_send_json_error();
            }
        }
    }

    public function update( $req ) {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $first_name = isset( $param['first_name'] )
            ? sanitize_text_field( $req['first_name'] )
            : null;
        $last_name = isset( $param['last_name'] )
            ? sanitize_text_field( $req['last_name'] )
            : null;
        $email = isset( $param['email'] )
            ? strtolower( sanitize_email( $req['email'] ) )
            : null;
        $name = isset( $param['name'] )
            ? sanitize_text_field( $req['name'] )
            : null;
        $web = isset( $param['web'] ) ? esc_url_raw( $req['web'] ) : null;
        $mobile = isset( $param['mobile'] )
            ? sanitize_text_field( $req['mobile'] )
            : null;
        $country = isset( $param['country'] )
            ? sanitize_text_field( $req['country'] )
            : '';
        $region = isset( $param['region'] )
            ? sanitize_text_field( $req['region'] )
            : '';
        $address = isset( $param['address'] )
            ? sanitize_text_field( $req['address'] )
            : null;
        $logo = isset( $param['logo'] ) ? absint( $param['logo'] ) : null;

        $client_portal = isset( $param['client_portal'] )
            ? rest_sanitize_boolean( $param['client_portal'] )
            : false;

        if ( empty( $name ) ) {
            $reg_errors->add(
                'field',
                esc_html__( 'Name field is missing', 'propovoice' )
            );
        }

        if ( ! is_email( $email ) ) {
            $reg_errors->add(
                'email_invalid',
                esc_html__( 'Email id is not valid!', 'propovoice' )
            );
        }

        if ( $reg_errors->get_error_messages() ) {
            wp_send_json_error( $reg_errors->get_error_messages() );
        } else {
            $url_params = $req->get_url_params();
            $post_id = $url_params['id'];

            $data = [
                'ID' => $post_id,
                'post_title' => $name,
                'post_author' => get_current_user_id(),
            ];
            $post_id = wp_update_post( $data );

            if ( ! is_wp_error( $post_id ) ) {
                if ( $first_name ) {
                    update_post_meta( $post_id, 'first_name', $first_name );
                }

                if ( $last_name ) {
                    update_post_meta( $post_id, 'last_name', $last_name );
                }

                if ( $email ) {
                    update_post_meta( $post_id, 'email', $email );
                }

                if ( $name ) {
                    update_post_meta( $post_id, 'name', $name );
                }

                if ( $web ) {
                    update_post_meta( $post_id, 'web', $web );
                }

                if ( $mobile ) {
                    update_post_meta( $post_id, 'mobile', $mobile );
                }

                update_post_meta( $post_id, 'country', $country );

                update_post_meta( $post_id, 'region', $region );

                if ( $address ) {
                    update_post_meta( $post_id, 'address', $address );
                }

                if ( $logo ) {
                    update_post_meta( $post_id, 'logo', $logo );
                } else {
                    delete_post_meta( $post_id, 'logo' );
                }

                if ( isset( $param['client_portal'] ) ) {
                    $client_model = new Client();
                    $client_model->set_user_if_not( $post_id, $first_name, $email, $client_portal );
                    update_post_meta( $post_id, 'client_portal', $client_portal );
                }

                do_action( 'ndpvp_webhook', 'contact_edit', $param );

                wp_send_json_success( $post_id );
            } else {
                wp_send_json_error();
            }
        }
    }

    public function delete( $req ) {
        $url_params = $req->get_url_params();

        $ids = explode( ',', $url_params['id'] );
        foreach ( $ids as $id ) {
            wp_delete_post( $id );
        }

        do_action( 'ndpvp_webhook', 'contact_del', $ids );

        wp_send_json_success( $ids );
    }

    // check permission
    public function get_per() {
        return current_user_can( 'ndpv_org' );
    }

    public function create_per() {
        return current_user_can( 'ndpv_org' );
    }

    public function update_per() {
        return current_user_can( 'ndpv_org' );
    }

    public function del_per() {
        return current_user_can( 'ndpv_org' );
    }
}
