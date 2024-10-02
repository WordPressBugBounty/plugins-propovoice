<?php

namespace Ndpv\Models;

use Ndpv\Helpers\Fns;

class Order {


    public function create( $param ) {
        $reg_errors = new \WP_Error();

        $package_id = isset( $param['package_id'] ) ? absint( $param['package_id'] ) : '';
        $payment_method = isset( $param['payment_method'] ) ? sanitize_text_field( $param['payment_method'] ) : '';
        $payment_info = isset( $param['payment_info'] )
            ? $param['payment_info']
            : '';

        if ( $reg_errors->get_error_messages() ) {
            return $reg_errors;
        } else {
            $data = [
                'post_type' => 'ndpv_package_order',
                'post_title'   => 'Package Order',
                'post_content' => '',
                'post_status'  => 'publish',
                'post_author'  => get_current_user_id(),
            ];
            $post_id = wp_insert_post( $data );

            if ( ! is_wp_error( $post_id ) ) {
                update_post_meta( $post_id, 'ws_id', ndpv()->get_workspace() );
                update_post_meta( $post_id, 'tab_id', $post_id ); //for task, note, file

                $user_id = get_current_user_id();
                $client_id = get_user_meta( $user_id, 'ndpv_client_id', true );

                if ( $package_id ) {
                    update_post_meta( $post_id, 'package_id', $package_id );
                }

                if ( $client_id ) {
                    update_post_meta( $post_id, 'client_id', $client_id );
                }

                $term_id = Fns::get_term_id_by_type( 'order_status', 'new' );
                if ( $term_id ) {
                    wp_set_post_terms(
                        $post_id,
                        [ $term_id ],
                        'ndpv_order_status'
                    );
                }

                $status = 'paid';
                if ( $payment_method === 'bank' ) {
                    $status = 'paid_req';
                }

                update_post_meta( $post_id, 'status', $status );
                update_post_meta( $post_id, 'payment_method', $payment_method );
                update_post_meta( $post_id, 'payment_info', $payment_info );
                return $post_id;
            } else {
                return $reg_errors->add( 'insert_package', esc_html__( 'Something wrong!', 'propovoice' ) );
            }
        }
    }

    public function single( $id ) {
        if ( ! $id ) {
			return null;
        }
        $data = [];

        $data['id'] = absint( $id );
        $meta = get_post_meta( $id );
        $data['name'] = isset( $meta['name'] ) ? $meta['name'][0] : '';
        $data['person_id'] = isset( $meta['person_id'] ) ? $meta['person_id'][0] : '';
        $data['first_name'] = '';
        if ( $data['person_id'] ) {
            $data['first_name'] = get_post_meta( $data['person_id'], 'first_name', true );
        }
        $data['email'] = isset( $meta['email'] ) ? $meta['email'][0] : '';
        $data['web'] = isset( $meta['web'] ) ? $meta['web'][0] : '';
        $data['mobile'] = isset( $meta['mobile'] ) ? $meta['mobile'][0] : '';
        $data['country'] = isset( $meta['country'] ) ? $meta['country'][0] : '';
        $data['region'] = isset( $meta['region'] ) ? $meta['region'][0] : '';
        $data['address'] = isset( $meta['address'] ) ? $meta['address'][0] : '';
        $data['address'] = isset( $meta['address'] ) ? $meta['address'][0] : '';
        $data['city'] = isset( $meta['city'] ) ? $meta['city'][0] : '';
        $data['zip'] = isset( $meta['zip'] ) ? $meta['zip'][0] : '';

        $data['is_client'] = isset( $meta['is_client'] )
            ? $meta['is_client'][0]
            : false;
        $data['client_portal'] = isset( $meta['client_portal'] )
            ? $meta['client_portal'][0]
            : false;
        $logo_id = isset( $meta['logo'] ) ? $meta['logo'][0] : '';
        $logo_data = null;
        if ( $logo_id ) {
            $logo_src = wp_get_attachment_image_src( $logo_id, 'thumbnail' );
            if ( $logo_src ) {
                $logo_data = [];
                $logo_data['id'] = $logo_id;
                $logo_data['src'] = $logo_src[0];
            }
        }
        $data['logo'] = $logo_data;

        return $data;
    }
}
