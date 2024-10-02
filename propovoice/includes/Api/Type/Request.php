<?php

namespace Ndpv\Api\Type;

use Ndpv\Helpers\Fns;
use Ndpv\Traits\Singleton;
use Ndpv\Models\Person;

class Request {

    use Singleton;

    public function routes() {
        register_rest_route(
            'ndpv/v1', '/requests/(?P<id>\d+)', [
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
            'ndpv/v1', '/requests' . ndpv()->plain_route(), [
				'methods' => 'GET',
				'callback' => [ $this, 'get' ],
				'permission_callback' => [ $this, 'get_per' ],
			]
        );

        register_rest_route(
            'ndpv/v1', '/requests', [
				'methods' => 'POST',
				'callback' => [ $this, 'create' ],
				'permission_callback' => [ $this, 'create_per' ],
			]
        );

        register_rest_route(
            'ndpv/v1', '/requests/(?P<id>\d+)', [
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
            'ndpv/v1', '/requests/(?P<id>[0-9,]+)', [
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

        if ( isset( $param['per_page'] ) ) {
            $per_page = $param['per_page'];
        }

        $module_id = isset( $param['module_id'] ) ? absint( $req['module_id'] ) : false;
        $dashboard = isset( $param['dashboard'] )
            ? sanitize_text_field( $req['dashboard'] )
            : false;
        $status_id = isset( $param['status_id'] )
            ? absint( $req['status_id'] )
            : false;

        if ( isset( $param['page'] ) && $param['page'] > 1 ) {
            $offset = $per_page * $param['page'] - $per_page;
        }

        if ( $dashboard ) {
            $per_page = 3;
        }

        $args = [
            'post_type' => 'ndpv_request',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ];

        if ( ! $module_id ) {
            $args['posts_per_page'] = $per_page;
            $args['offset'] = $offset;
        }

        $args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            'relation' => 'OR',
        ];

        if ( $module_id ) {
            $args['meta_query'][] = [
                [
                    'key' => 'tab_id',
                    'value' => $module_id,
                    'compare' => '=',
                ],
            ];
        }

        if ( ! $status_id ) {
            $taxonomy = 'request_status';
            $get_taxonomy = Fns::get_terms( $taxonomy );
            $status_id = $get_taxonomy[0]->term_id;
        }

        if ( $dashboard ) {
            $tax_args = [
                'hide_empty' => false, // also retrieve terms which are not used yet
                'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                    [
                        'key'      => 'type',
                        'value'    => 'done',
                        'compare'  => 'LIKE',
                    ],
                ],
                'taxonomy'  => 'ndpv_request_status',
            ];
            $terms = get_terms( $tax_args );
            $status_id = $terms[0]->term_id;

            $args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                [
                    'taxonomy' => 'ndpv_request_status',
                    'terms' => $status_id,
                    'field' => 'term_id',
                    'operator'  => 'NOT IN',
                ],
            ];
        }

        if ( current_user_can( 'ndpv_staff' ) ) {
            $post_ids = Fns::get_posts_ids_by_type( 'ndpv_request' );
            if ( ! empty( $post_ids ) ) {
                $args['post__in'] = $post_ids;
                $args['orderby'] = 'post__in';
            } else {
                $args['author'] = get_current_user_id();
            }
        }

        $query = new \WP_Query( $args );
        $total_data = $query->found_posts; //use this for pagination
        $result = [];
        $data = [];

        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();

            $query_data = [];
            $query_data['id'] = $id;

            $query_meta = get_post_meta( $id );
            $query_data['title'] = get_the_title();

            $query_data['package'] = [];

            $order_id = get_post_meta( $id, 'tab_id', true );

            $package_id = get_post_meta( $order_id, 'package_id', true );
            $package_meta = get_post_meta( $package_id );
            $query_data['package']['id'] = $package_id;
            // $query_data["package"]["status"] = get_post_status($package_id);
            $query_data['package']['title'] = get_the_title( $package_id );
            $img_id = isset( $package_meta['img'] ) ? $package_meta['img'][0] : '';
            $img_data = null;
            if ( $img_id ) {
                $img_src = wp_get_attachment_image_src( $img_id, 'thumbnail' );
                if ( $img_src ) {
                    $img_data = [];
                    $img_data['id'] = $img_id;
                    $img_data['src'] = $img_src[0];
                }
            }
            $query_data['package']['img'] = $img_data;

            $query_data['person'] = null;
            $person_id = get_post_meta( $order_id, 'client_id', true );
            if ( $person_id ) {
                $person = new Person();
                $query_data['person'] = $person->single( $person_id );
            }

            //when request accepted get project id
            $query_data['project_id'] = get_post_meta( $id, 'project_id', true );

            $query_data['status_id'] = '';
            $status = '';
            if ( $query_data['project_id'] ) {
                $status = get_the_terms( $query_data['project_id'], 'ndpv_project_status' );
            } else {
                $status = get_the_terms( $id, 'ndpv_request_status' );
            }
            if ( $status ) {
                $term_id = $status[0]->term_id;
                $query_data['status_id'] = [
                    'id' => $term_id,
                    'label' => $status[0]->name,
                    'color' => '#4a5568',
                    'bg_color' => '#E2E8F0',
                    'type' => get_term_meta( $term_id, 'type', true ),
                ];

                $color = get_term_meta( $term_id, 'color', true );
                $bg_color = get_term_meta( $term_id, 'bg_color', true );

                if ( $color ) {
                    $query_data['status_id']['color'] = $color;
                }

                if ( $bg_color ) {
                    $query_data['status_id']['bg_color'] = $bg_color;
                }
            }

            $query_data['date'] = get_the_time( get_option( 'date_format' ) );

            $data[] = $query_data;
        }
        wp_reset_postdata();

        $result['result'] = $data;

        $taxonomy = 'request_status';
        $get_taxonomy = Fns::get_terms( $taxonomy );

        $request_taxonomy = [];
        foreach ( $get_taxonomy as $single ) {
            $request_taxonomy[] = [
                'id' => $single->term_id,
                'label' => $single->name,
            ];
        }

        $package_id = get_post_meta( $module_id, 'package_id', true );

        $result['extra'] = [
            'custom_field' => Fns::custom_field( 'package_req_form_' . $package_id ),
            'request_status' => $request_taxonomy,
        ];
        $result['total'] = $total_data;

        wp_send_json_success( $result );
    }

    public function get_single( $req ) {
        $url_params = $req->get_url_params();
        $id = $url_params['id'];
        $query_data = [];
        $query_data['id'] = $id;

        $query_meta = get_post_meta( $id );
        $query_data['tab_id'] = isset( $query_meta['tab_id'] )
            ? absint( $query_meta['tab_id'][0] )
            : '';

        $query_data['title'] = get_the_title( $id );
        $query_data['desc'] = get_post_field( 'post_content', $id );

        $query_data['person'] = null;
        $order_id = get_post_meta( $id, 'tab_id', true );
        $person_id = get_post_meta( $order_id, 'client_id', true );
        if ( $person_id ) {
            $person = new Person();
            $query_data['person'] = $person->single( $person_id );
        }

        //when request accepted get project id
        $query_data['project_id'] = get_post_meta( $id, 'project_id', true );

        $query_data['status_id'] = '';
        $status = get_the_terms( $id, 'ndpv_request_status' );
        if ( $status ) {
            $term_id = $status[0]->term_id;
            $query_data['status_id'] = [
                'id' => $term_id,
                'label' => $status[0]->name,
                'color' => '#4a5568',
                'bg_color' => '#E2E8F0',
                'type' => get_term_meta( $term_id, 'type', true ),
            ];

            $color = get_term_meta( $term_id, 'color', true );
            $bg_color = get_term_meta( $term_id, 'bg_color', true );

            if ( $color ) {
                $query_data['status_id']['color'] = $color;
            }

            if ( $bg_color ) {
                $query_data['status_id']['bg_color'] = $bg_color;
            }
        }

        $order_id = get_post_meta( $id, 'tab_id', true );

        $package_id = get_post_meta( $order_id, 'package_id', true );

        $query_data['package'] = [];
        $query_data['package']['id'] = $package_id;
        $query_data['package']['title'] = get_the_title( $package_id );

        //custom field
        foreach ( Fns::custom_field( 'package_req_form_' . $package_id ) as $value ) {
            if ( $value['type'] === 'multi-select' ) {
                $query_data[ $value['slug'] ] = isset( $query_meta[ $value['slug'] ] )
                    ? maybe_unserialize( $query_meta[ $value['slug'] ][0] )
                    : '';
            } else {
                $query_data[ $value['slug'] ] = isset( $query_meta[ $value['slug'] ] )
                    ? $query_meta[ $value['slug'] ][0]
                    : '';
            }
        }

        $query_data['custom_field'] = Fns::custom_field( 'package_req_form_' . $package_id );

        wp_send_json_success( $query_data );
    }

    public function create( $req ) {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $tab_id = isset( $param['tab_id'] ) ? absint( $req['tab_id'] ) : null;
        $title = isset( $param['title'] )
            ? sanitize_text_field( $req['title'] )
            : '';
        $desc = isset( $param['desc'] )
            ? ( $req['desc'] )
            : '';
        $img = isset( $param['img'] )
            ? absint( $param['img'] )
            : null;

        if ( empty( $title ) ) {
            $reg_errors->add(
                'field',
                esc_html__( 'Title field is missing', 'propovoice' )
            );
        }

        $package_id = get_post_meta( $tab_id, 'package_id', true );
        $max_req = get_post_meta( $package_id, 'max_req', true );
        $max_req_at_a_time = get_post_meta( $package_id, 'max_req_at_a_time', true );

        if ( $max_req ) {
            $month_total = Fns::project_request_limit( $tab_id, true );
            if ( $month_total >= $max_req ) {
                $reg_errors->add(
                    'field',
                    esc_html__( 'Your request limit exceeded', 'propovoice' )
                );
            }
        }

        if ( $max_req_at_a_time ) {
            $total = Fns::project_request_limit( $tab_id );
            if ( $total >= $max_req_at_a_time ) {
                $reg_errors->add(
                    'field',
                    esc_html__( 'Your at a time request limit exceeded', 'propovoice' )
                );
            }
        }

        if ( $reg_errors->get_error_messages() ) {
            wp_send_json_error( $reg_errors->get_error_messages() );
        } else {
            $data = [
                'post_type' => 'ndpv_request',
                'post_title' => $title,
                'post_content' => $desc,
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
            ];
            $post_id = wp_insert_post( $data );

            if ( ! is_wp_error( $post_id ) ) {
                update_post_meta( $post_id, 'ws_id', ndpv()->get_workspace() );

                if ( $tab_id ) {
                    update_post_meta( $post_id, 'tab_id', $tab_id );
                }

                $term_id = Fns::get_term_id_by_type( 'request_status', 'new' );
                if ( $term_id ) {
                    wp_set_post_terms(
                        $post_id,
                        [ $term_id ],
                        'ndpv_request_status'
                    );
                }

                if ( isset( $param['img'] ) ) {
                    if ( $img ) {
                        update_post_meta( $post_id, 'img', $img );
                    }
                }

                //custom field
                $package_id = get_post_meta( $tab_id, 'package_id', true );
                foreach ( Fns::custom_field( 'package_req_form_' . $package_id ) as $value ) {
                    $field = '';
                    if ( $value['type'] === 'multi-select' ) {
                        $field = isset( $param[ $value['slug'] ] )
                            ? array_map( 'sanitize_text_field', $param[ $value['slug'] ] )
                            : '';
                    } else {
                        $field = isset( $param[ $value['slug'] ] )
                            ? sanitize_text_field( $param[ $value['slug'] ] )
                            : '';
                    }

                    if ( $field ) {
                        update_post_meta( $post_id, $value['slug'], $field );
                    }
                }

                $param['id'] = $post_id;
                do_action( 'ndpvp_webhook', 'request_add', $param );

                wp_send_json_success( $post_id );
            } else {
                wp_send_json_error();
            }
        }
    }

    public function update( $req ) {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $title = isset( $param['title'] )
            ? sanitize_text_field( $req['title'] )
            : '';
        $desc = isset( $param['desc'] )
            ? ( $req['desc'] )
            : '';
        $img = isset( $param['img'] )
            ? absint( $param['img'] )
            : null;

        if ( $reg_errors->get_error_messages() ) {
            wp_send_json_error( $reg_errors->get_error_messages() );
        } else {
            $url_params = $req->get_url_params();
            $post_id = $url_params['id'];

            $data = [
                'ID' => $post_id,
                'post_author' => get_current_user_id(),
            ];

            if ( isset( $param['title'] ) ) {
                $data['post_title'] = sanitize_text_field( $req['title'] );
            }

            if ( isset( $param['desc'] ) ) {
                $data['post_content'] = sanitize_text_field( $req['desc'] );
            }

            $post_id = wp_update_post( $data );

            if ( ! is_wp_error( $post_id ) ) {
                do_action( 'ndpvp_webhook', 'request_edit', $param );

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

        do_action( 'ndpvp_webhook', 'request_del', $ids );

        wp_send_json_success( $ids );
    }

    // check permission
    public function get_per() {
        return current_user_can( 'ndpv_request' );
    }

    public function create_per() {
        return current_user_can( 'ndpv_request' );
    }

    public function update_per() {
        return current_user_can( 'ndpv_request' );
    }

    public function del_per() {
        return current_user_can( 'ndpv_request' );
    }
}
