<?php
namespace Ndpv\Models;

use Ndpv\Helpers\Fns;

class Org {


    public function create( $param ) {
        $reg_errors = new \WP_Error();

        $name   = isset( $param['org_name'] ) ? sanitize_text_field( $param['org_name'] ) : '';
        $person_id     = isset( $param['person_id'] ) ? sanitize_text_field( $param['person_id'] ) : null;
        $email        = isset( $param['email'] ) ? strtolower( sanitize_email( $param['email'] ) ) : '';
        $web          = isset( $param['web'] ) ? esc_url_raw( $param['web'] ) : '';
        $mobile       = isset( $param['mobile'] ) ? sanitize_text_field( $param['mobile'] ) : '';
        $country      = isset( $param['country'] ) ? sanitize_text_field( $param['country'] ) : '';
        $region       = isset( $param['region'] ) ? sanitize_text_field( $param['region'] ) : '';
        $address      = isset( $param['address'] ) ? sanitize_text_field( $param['address'] ) : '';
        $city         = isset( $param['city'] ) ? sanitize_text_field( $param['city'] ) : '';
        $zip          = isset( $param['zip'] ) ? sanitize_text_field( $param['zip'] ) : '';
        $logo = isset( $param['logo'] ) ? absint( $param['logo'] ) : null;
        $is_client  = isset( $param['is_client'] ) ? $param['is_client'] : false;

        if ( ! $person_id ) {
            $exist_id = Fns::contact_exist( 'org', $email );
            if ( $exist_id ) {
                if ( $is_client ) {
                    update_post_meta( $exist_id, 'is_client', $is_client );
                }
                return $exist_id;
            }
        }

        if ( $reg_errors->get_error_messages() ) {
            return $reg_errors;
        } else {
            $data = [
                'post_type' => 'ndpv_org',
                'post_title'    => $name,
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_author'   => get_current_user_id(),
            ];
            $post_id = wp_insert_post( $data );

            if ( ! is_wp_error( $post_id ) ) {
                update_post_meta( $post_id, 'ws_id', ndpv()->get_workspace() );

                if ( $name ) {
                    update_post_meta( $post_id, 'name', $name );
                }

                if ( $email ) {
                    update_post_meta( $post_id, 'email', $email );
                }

                if ( $person_id ) {
                    update_post_meta( $post_id, 'person_id', $person_id );
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

                if ( $logo ) {
                    update_post_meta( $post_id, 'logo', $logo );
                }

                return $post_id;
            } else {
                return $reg_errors->add( 'insert_post', esc_html__( 'Something wrong!', 'propovoice' ) );
            }
        }
    }

    public function update( $param ) {
        $reg_errors = new \WP_Error();

        $name         = isset( $param['org_name'] ) ? sanitize_text_field( $param['org_name'] ) : '';
        $email        = isset( $param['email'] ) ? strtolower( sanitize_email( $param['email'] ) ) : '';
        $person_id    = isset( $param['person_id'] ) ? sanitize_text_field( $param['person_id'] ) : '';
        $web          = isset( $param['web'] ) ? esc_url_raw( $param['web'] ) : '';
        $mobile       = isset( $param['mobile'] ) ? sanitize_text_field( $param['mobile'] ) : '';
        $country      = isset( $param['country'] ) ? sanitize_text_field( $param['country'] ) : '';
        $region       = isset( $param['region'] ) ? sanitize_text_field( $param['region'] ) : '';
        $address      = isset( $param['address'] ) ? sanitize_text_field( $param['address'] ) : '';
        $city         = isset( $param['city'] ) ? sanitize_text_field( $param['city'] ) : '';
        $zip          = isset( $param['zip'] ) ? sanitize_text_field( $param['zip'] ) : '';
        $logo         = isset( $param['logo'] ) ? absint( $param['logo'] ) : '';
        $is_client    = isset( $param['is_client'] ) ? $param['is_client'] : false;

        if ( $reg_errors->get_error_messages() ) {
            return $reg_errors;
        } else {
            $post_id = $param['org_id'];

            $data = [
                'ID'            => $post_id,
                'post_title'    => $name,
                'post_author'   => get_current_user_id(),
            ];
            $post_id = wp_update_post( $data );

            if ( ! is_wp_error( $post_id ) ) {
                if ( isset( $param['org_name'] ) ) {
                    update_post_meta( $post_id, 'name', $name );
                }

                if ( isset( $param['email'] ) ) {
                    update_post_meta( $post_id, 'email', $email );
                }

                if ( $person_id ) {
                    update_post_meta( $post_id, 'person_id', $person_id );
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

                if ( isset( $param['logo'] ) ) {
                    if ( $logo ) {
                        update_post_meta( $post_id, 'logo', $logo );
                    } else {
                        delete_post_meta( $post_id, 'logo' );
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

    function single( $id ) {
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
