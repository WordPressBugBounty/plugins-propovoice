<?php

namespace Ndpv\Api\Type;

use Ndpv\Models\Client;
use Ndpv\Models\Org;
use Ndpv\Traits\Singleton;

class Person {

    use Singleton;

    public function routes() {
        register_rest_route(
            'ndpv/v1', '/persons/(?P<id>\d+)', [
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
            'ndpv/v1', '/persons' . ndpv()->plain_route(), [
				'methods' => 'GET',
				'callback' => [ $this, 'get' ],
				'permission_callback' => [ $this, 'get_per' ],
			]
        );

        register_rest_route(
            'ndpv/v1', '/persons', [
				'methods' => 'POST',
				'callback' => [ $this, 'create' ],
				'permission_callback' => [ $this, 'create_per' ],
			]
        );

        register_rest_route(
            'ndpv/v1', '/persons/(?P<id>\d+)', [
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
            'ndpv/v1', '/persons/(?P<id>[0-9,]+)', [
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
        $s = isset( $param['text'] ) ? sanitize_text_field( $param['text'] ) : null;

        //for searching contact from other module
        $first_name = isset( $param['first_name'] ) ? sanitize_text_field( $param['first_name'] ) : '';
        if ( $first_name ) {
            $s = $first_name;
        }

        if ( isset( $param['per_page'] ) ) {
            $per_page = $param['per_page'];
        }

        if ( isset( $param['page'] ) && $param['page'] > 1 ) {
            $offset = $per_page * $param['page'] - $per_page;
        }

        $args = [
            'post_type' => 'ndpv_person',
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
                    'key' => 'first_name',
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
            $query_data['first_name'] = isset( $query_meta['first_name'] )
                ? $query_meta['first_name'][0]
                : '';
            $query_data['org_id'] = isset( $query_meta['org_id'] )
                ? $query_meta['org_id'][0]
                : '';
            $query_data['org_name'] = isset( $query_meta['org_id'] ) && $query_meta['org_id']
                ? get_post_meta( $query_data['org_id'], 'name', true )
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
            $query_data['city'] = isset( $query_meta['city'] )
                ? $query_meta['city'][0]
                : '';
            $query_data['zip'] = isset( $query_meta['zip'] )
                ? $query_meta['zip'][0]
                : '';

            $img_id = isset( $query_meta['img'] ) ? $query_meta['img'][0] : null;
            $img_data = null;
            if ( $img_id ) {
                $img_src = wp_get_attachment_image_src( $img_id, 'thumbnail' );
                if ( $img_src ) {
                    $img_data = [];
                    $img_data['id'] = $img_id;
                    $img_data['src'] = $img_src[0];
                }
            }
            $query_data['img'] = $img_data;

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
        $query_data['id'] = absint( $id );
        $query_data['tab_id'] = $id;

        $query_data['level_id'] = '';

        $level = get_the_terms( $id, 'ndpv_lead_level' );
        if ( $level ) {
            $query_data['level_id'] = [
                'id' => $level[0]->term_id,
                'label' => $level[0]->name,
            ];
        }

        $query_data['tags'] = [];

        $tags = get_the_terms( $id, 'ndpv_tag' );
        if ( $tags ) {
            $tag_list = [];
            foreach ( $tags as $tag ) {
                $tag_list[] = [
                    'id' => $tag->term_id,
                    'label' => $tag->name,
                ];
            }
            $query_data['tags'] = $tag_list;
        }

        $person_id = $id;
        $person_data = [];

        if ( $person_id ) {
            $person_data['id'] = absint( $person_id );
            $person_meta = get_post_meta( $person_id );
            $person_data['first_name'] = isset( $person_meta['first_name'] )
                ? $person_meta['first_name'][0]
                : '';
            $person_data['last_name'] = isset( $person_meta['last_name'] )
                ? $person_meta['last_name'][0]
                : '';
            $person_data['org_name'] = isset( $person_meta['org_name'] )
                ? $person_meta['org_name'][0]
                : '';
            $person_data['email'] = isset( $person_meta['email'] )
                ? $person_meta['email'][0]
                : '';
            $person_data['mobile'] = isset( $person_meta['mobile'] )
                ? $person_meta['mobile'][0]
                : '';
            $person_data['web'] = isset( $person_meta['web'] )
                ? $person_meta['web'][0]
                : '';
            $person_data['country'] = isset( $person_meta['country'] )
                ? $person_meta['country'][0]
                : '';
            $person_data['region'] = isset( $person_meta['region'] )
                ? $person_meta['region'][0]
                : '';
            $person_data['address'] = isset( $person_meta['address'] )
                ? $person_meta['address'][0]
                : '';
            $person_data['city'] = isset( $person_meta['city'] )
                ? $person_meta['city'][0]
                : '';
            $person_data['zip'] = isset( $person_meta['zip'] )
                ? $person_meta['zip'][0]
                : '';

            $person_data['client_portal'] = isset( $person_meta['client_portal'] )
                ? $person_meta['client_portal'][0]
                : false;

            $img_id = isset( $person_meta['img'] ) ? $person_meta['img'][0] : null;
            $img_data = null;
            if ( $img_id ) {
                $img_src = wp_get_attachment_image_src( $img_id, 'thumbnail' );
                if ( $img_src ) {
                    $img_data = [];
                    $img_data['id'] = $img_id;
                    $img_data['src'] = $img_src[0];
                }
            }
            $person_data['img'] = $img_data;
        }
        $query_data['person'] = $person_data;

        $query_data['date'] = get_the_time( get_option( 'date_format' ) );

        wp_send_json_success( $query_data );
    }

    public function create( $req ) {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $first_name = isset( $param['first_name'] )
            ? sanitize_text_field( $req['first_name'] )
            : null;
        $org_name = isset( $param['org_name'] )
            ? sanitize_text_field( $req['org_name'] )
            : null;
        $org_id = isset( $param['org_id'] ) ? absint( $param['org_id'] ) : null;
        $email = isset( $param['email'] )
            ? strtolower( sanitize_email( $req['email'] ) )
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
            : '';
        $city = isset( $param['city'] )
            ? sanitize_text_field( $req['city'] )
            : '';
        $zip = isset( $param['zip'] )
            ? sanitize_text_field( $req['zip'] )
            : '';
        $img = isset( $param['img'] ) ? absint( $param['img'] ) : null;

        if ( empty( $first_name ) ) {
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
        $person_id = $this->is_person_exists( $email, $mobile );

        if ( $person_id ) {
            $reg_errors->add(
                'already_exist',
                esc_html__( 'Contact already exists!', 'propovoice' )
            );

            wp_send_json_error( $reg_errors->get_error_messages() );
        }
        if ( $reg_errors->get_error_messages() ) {
            wp_send_json_error( $reg_errors->get_error_messages() );
        } else {
            $data = [
                'post_type' => 'ndpv_person',
                'post_title' => $first_name,
                'post_content' => '',
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
            ];
            $post_id = wp_insert_post( $data );

            if ( ! is_wp_error( $post_id ) ) {
                update_post_meta( $post_id, 'ws_id', ndpv()->get_workspace() );

                if ( $first_name ) {
                    update_post_meta( $post_id, 'first_name', $first_name );
                }

                if ( ! $org_id && $org_name ) {
                    $org = new Org();
                    $org_id = $org->create(
                        [
							'org_name' => $org_name,
							'person_id' => $post_id,
						]
                    );
                }

                if ( $org_id ) {
                    update_post_meta( $post_id, 'org_id', $org_id );
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

                if ( $city ) {
                    update_post_meta( $post_id, 'city', $city );
                }

                if ( $zip ) {
                    update_post_meta( $post_id, 'zip', $zip );
                }

                if ( $img ) {
                    update_post_meta( $post_id, 'img', $img );
                }

                do_action( 'ndpvp_webhook', 'contact_add', $param );

                wp_send_json_success( $post_id );
            } else {
                wp_send_json_error();
            }
        }
    }

    public function is_person_exists( $email, $mobile ) {
        $args = [
            'post_type' => [ 'ndpv_person' ],
            'post_status' => 'publish',
            'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                'relation' => 'OR',
                [
                    'key'     => 'email',
                    'value'   => $email,
                    'compare' => '=',
                ],
            ],
            'fields' => 'ids',
            'posts_per_page' => 1,
        ];

        if ( $mobile ) {
            $args['meta_query'][] = [
                'key'     => 'mobile',
                'value'   => $mobile,
                'compare' => '=',
            ];
        }

        $posts = get_posts( $args );

        if ( $posts ) {
            $post_id = $posts[0];
            return $post_id;
        } else {
            return false;
        }
    }

    public function update( $req ) {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $first_name = isset( $param['first_name'] )
            ? sanitize_text_field( $req['first_name'] )
            : null;
        $org_id = isset( $param['org_id'] ) ? absint( $param['org_id'] ) : null;
        $org_name = isset( $param['org_name'] )
            ? sanitize_text_field( $req['org_name'] )
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
            : '';
        $region = isset( $param['region'] )
            ? sanitize_text_field( $req['region'] )
            : '';
        $address = isset( $param['address'] )
            ? sanitize_text_field( $req['address'] )
            : '';
        $city = isset( $param['city'] )
            ? sanitize_text_field( $req['city'] )
            : '';
        $zip = isset( $param['zip'] )
            ? sanitize_text_field( $req['zip'] )
            : '';
        $img = isset( $param['img'] ) ? absint( $param['img'] ) : null;

        $client_portal = isset( $param['client_portal'] )
            ? rest_sanitize_boolean( $param['client_portal'] )
            : false;

        if ( empty( $first_name ) ) {
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
                'post_title' => $first_name,
                'post_author' => get_current_user_id(),
            ];
            $post_id = wp_update_post( $data );

            if ( ! is_wp_error( $post_id ) ) {
                if ( isset( $param['first_name'] ) ) {
                    update_post_meta( $post_id, 'first_name', $first_name );
                }

                if ( isset( $param['org_name'] ) ) {
                    update_post_meta( $post_id, 'org_name', $org_name );
                }

                if ( isset( $param['email'] ) ) {
                    update_post_meta( $post_id, 'email', $email );
                }

                if ( isset( $param['web'] ) ) {
                    update_post_meta( $post_id, 'web', $web );
                }

                if ( isset( $param['mobile'] ) ) {
                    update_post_meta( $post_id, 'mobile', $mobile );
                }

                if ( isset( $param['country'] ) ) {
                    update_post_meta( $post_id, 'country', $country );
                }

                if ( isset( $param['region'] ) ) {
                    update_post_meta( $post_id, 'region', $region );
                }

                if ( isset( $param['address'] ) ) {
                    update_post_meta( $post_id, 'address', $address );
                }

                if ( isset( $param['city'] ) ) {
                    update_post_meta( $post_id, 'city', $city );
                }

                if ( isset( $param['zip'] ) ) {
                    update_post_meta( $post_id, 'zip', $zip );
                }

                $org = new Org();
                if ( ! $org_id && $org_name ) {
                    $org_id = $org->create(
                        [
							'org_name' => $org_name,
							'person_id' => $post_id,
						]
                    );
                } elseif ( $org_id && $org_name ) {
                    $org->update(
                        [
							'org_id' => $org_id,
							'org_name' => $org_name,
						]
                    );
                }

                if ( $org_id && ! $org_name ) {
                    update_post_meta( $post_id, 'org_id', null );
                } elseif ( $org_id ) {
                    update_post_meta( $post_id, 'org_id', $org_id );
                }

                if ( isset( $param['img'] ) ) {
                    if ( $img ) {
                        update_post_meta( $post_id, 'img', $img );
                    } else {
                        delete_post_meta( $post_id, 'img' );
                    }
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
        return current_user_can( 'ndpv_person' );
    }

    public function create_per() {
        return current_user_can( 'ndpv_person' );
    }

    public function update_per() {
        return current_user_can( 'ndpv_person' );
    }

    public function del_per() {
        return current_user_can( 'ndpv_person' );
    }
}
