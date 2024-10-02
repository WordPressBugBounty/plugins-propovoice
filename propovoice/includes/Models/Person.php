<?php
namespace Ndpv\Models;

use Ndpv\Helpers\Fns;

class Person {

    public function create( $param ) {
        $reg_errors = new \WP_Error();

        $org_id       = isset( $param['org_id'] ) ? sanitize_text_field( $param['org_id'] ) : null;
        $first_name   = isset( $param['first_name'] ) ? sanitize_text_field( $param['first_name'] ) : '';
        $email        = isset( $param['email'] ) ? strtolower( sanitize_email( $param['email'] ) ) : '';
        $web          = isset( $param['web'] ) ? esc_url_raw( $param['web'] ) : '';
        $mobile       = isset( $param['mobile'] ) ? sanitize_text_field( $param['mobile'] ) : '';
        $country      = isset( $param['country'] ) ? sanitize_text_field( $param['country'] ) : '';
        $region       = isset( $param['region'] ) ? sanitize_text_field( $param['region'] ) : '';
        $address      = isset( $param['address'] ) ? sanitize_text_field( $param['address'] ) : '';
        $city         = isset( $param['city'] ) ? sanitize_text_field( $param['city'] ) : '';
        $zip          = isset( $param['zip'] ) ? sanitize_text_field( $param['zip'] ) : '';
        $img          = isset( $param['img'] ) ? absint( $param['img'] ) : null;
        $is_client    = isset( $param['is_client'] ) ? $param['is_client'] : false;

        $exist_id = Fns::contact_exist( 'person', $email );
        if ( $exist_id ) {
            if ( $is_client ) {
                update_post_meta( $exist_id, 'is_client', $is_client );
            }
            return $exist_id;
        }

        if ( $reg_errors->get_error_messages() ) {
            return $reg_errors;
        } else {
            $data = [
                'post_type' => 'ndpv_person',
                'post_title'    => $first_name,
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_author'   => get_current_user_id(),
            ];
            $post_id = wp_insert_post( $data );

            if ( ! is_wp_error( $post_id ) ) {
                update_post_meta( $post_id, 'ws_id', ndpv()->get_workspace() );

                if ( $org_id ) {
                    update_post_meta( $post_id, 'org_id', $org_id );
                }

                if ( $first_name ) {
                    update_post_meta( $post_id, 'first_name', $first_name );
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

                if ( $is_client ) {
                    update_post_meta( $post_id, 'is_client', $is_client );
                }

                return $post_id;
            } else {
                return $reg_errors->add( 'insert_post', esc_html__( 'Something wrong!', 'propovoice' ) );
            }
        }
    }

    public function update( $param ) {
        $reg_errors = new \WP_Error();

        $first_name   = isset( $param['first_name'] ) ? sanitize_text_field( $param['first_name'] ) : '';
        $email        = isset( $param['email'] ) ? strtolower( sanitize_email( $param['email'] ) ) : '';
        $org_id       = isset( $param['org_id'] ) ? sanitize_text_field( $param['org_id'] ) : null;
        $web          = isset( $param['web'] ) ? esc_url_raw( $param['web'] ) : '';
        $mobile       = isset( $param['mobile'] ) ? sanitize_text_field( $param['mobile'] ) : '';
        $country      = isset( $param['country'] ) ? sanitize_text_field( $param['country'] ) : '';
        $region       = isset( $param['region'] ) ? sanitize_text_field( $param['region'] ) : '';
        $address      = isset( $param['address'] ) ? sanitize_text_field( $param['address'] ) : '';
        $city         = isset( $param['city'] ) ? sanitize_text_field( $param['city'] ) : '';
        $zip          = isset( $param['zip'] ) ? sanitize_text_field( $param['zip'] ) : '';
        $img = isset( $param['img'] ) ? absint( $param['img'] ) : null;
        $is_client  = isset( $param['is_client'] ) ? $param['is_client'] : false;

        if ( $reg_errors->get_error_messages() ) {
            return $reg_errors;
        } else {
            $post_id = $param['person_id'];

            $data = [
                'ID'            => $post_id,
                'post_title'    => $first_name,
                'post_author'   => get_current_user_id(),
            ];
            $post_id = wp_update_post( $data );

            if ( ! is_wp_error( $post_id ) ) {
                if ( $first_name ) {
                    update_post_meta( $post_id, 'first_name', $first_name );
                }

                if ( $email ) {
                    update_post_meta( $post_id, 'email', $email );
                }

                if ( $org_id ) {
                    update_post_meta( $post_id, 'org_id', $org_id );
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

                if ( isset( $param['img'] ) ) {
                    if ( $img ) {
                        update_post_meta( $post_id, 'img', $img );
                    } else {
                        delete_post_meta( $post_id, 'img' );
                    }
                }

                if ( $is_client ) {
                    update_post_meta( $post_id, 'is_client', $is_client );
                }

                return $post_id;
            } else {
                return $reg_errors->add( 'update_post', esc_html__( 'Something wrong!', 'propovoice' ) );
            }
        }
    }

    public function single( $id, $details = false ) {
        if ( ! $id ) {
            return null;
        }
        $data = [];

        $data['id'] = absint( $id );
        $meta = get_post_meta( $id );
        $data['first_name'] = isset( $meta['first_name'] ) ? $meta['first_name'][0] : '';
        $data['org_id'] = isset( $meta['org_id'] ) ? $meta['org_id'][0] : '';
        $data['org_name'] = '';
        if ( $data['org_id'] ) {
            $data['org_name'] = get_post_meta( $data['org_id'], 'name', true );
        }
        $data['email'] = isset( $meta['email'] ) ? $meta['email'][0] : '';
        $data['web'] = isset( $meta['web'] ) ? $meta['web'][0] : '';
        $data['mobile'] = isset( $meta['mobile'] ) ? $meta['mobile'][0] : '';
        $data['country'] = isset( $meta['country'] ) ? $meta['country'][0] : '';
        $data['region'] = isset( $meta['region'] ) ? $meta['region'][0] : '';
        $data['address'] = isset( $meta['address'] ) ? $meta['address'][0] : '';
        $data['city'] = isset( $meta['city'] ) ? $meta['city'][0] : '';
        $data['zip'] = isset( $meta['zip'] ) ? $meta['zip'][0] : '';

        $data['is_client'] = isset( $meta['is_client'] )
            ? $meta['is_client'][0]
            : false;
        $data['client_portal'] = isset( $meta['client_portal'] )
            ? $meta['client_portal'][0]
            : false;
        $img_id = isset( $meta['img'] ) ? $meta['img'][0] : '';
        $img_data = null;
        if ( $img_id ) {
            $img_src = wp_get_attachment_image_src( $img_id, 'thumbnail' );
            if ( $img_src ) {
                $img_data = [];
                $img_data['id'] = $img_id;
                $img_data['src'] = $img_src[0];
            }
        }
        $data['img'] = $img_data;

        return $data;
    }
}
