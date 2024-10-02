<?php

namespace Ndpv\Model;

use Ndpv\Helper\Fns;
use Ndpv\Model\Org;
use Ndpv\Model\Person;

class Lead {

	public function create( $data ) {

		$param = $data;

		// lead
		$first_name = isset( $param['first_name'] ) ? sanitize_text_field( $param['first_name'] ) : null;
		$org_name   = isset( $param['org_name'] ) ? sanitize_text_field( $param['org_name'] ) : null;
		$person_id  = isset( $param['person_id'] ) ? absint( $param['person_id'] ) : null;
		$org_id     = isset( $param['org_id'] ) ? absint( $param['org_id'] ) : null;
		$level_id   = isset( $param['level_id'] ) ? absint( $param['level_id'] ) : null;
		$budget     = isset( $param['budget'] ) ? sanitize_text_field( $param['budget'] ) : null;
		$currency   = isset( $param['currency'] ) ? sanitize_text_field( $param['currency'] ) : null;
		$tags       = isset( $param['tags'] ) ? array_map( 'absint', $param['tags'] ) : null;
		$desc       = isset( $param['desc'] ) ? nl2br( $param['desc'] ) : '';

		$person = new Person();
		if ( $person_id ) {
			$person->update( $param );
		}

		if ( ! $person_id && $first_name ) {
			$person_id = $person->create( $param );
		}

		$org = new Org();
		if ( ! $person_id && $org_id ) {
			$org->update( $param );
		}

		if ( ! $org_id && $org_name ) {
			$org_id = $org->create( $param );
		}

		// insert lead
		$data = array(
			'post_type'    => 'ndpv_lead',
			'post_title'   => 'Lead',
			'post_content' => $desc,
			'post_status'  => 'publish',
			// 'post_author'  => null
		);
		$post_id = wp_insert_post( $data );

		if ( ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, 'ws_id', ndpv()->get_workspace() );
			update_post_meta( $post_id, 'tab_id', $post_id ); // for task, note, file

			if ( $level_id ) {
				wp_set_post_terms( $post_id, array( $level_id ), 'ndpv_lead_level' );
			}

			if ( $person_id ) {
				update_post_meta( $post_id, 'person_id', $person_id );
			}

			if ( $org_id ) {
				update_post_meta( $post_id, 'org_id', $org_id );
			}

			if ( $budget ) {
				update_post_meta( $post_id, 'budget', $budget );
			}

			if ( $currency ) {
				update_post_meta( $post_id, 'currency', $currency );
			}

			if ( $tags ) {
				wp_set_post_terms( $post_id, $tags, 'ndpv_tag' );
			}

			// custom field
			foreach ( Fns::custom_field( 'lead' ) as $value ) {
				$field = '';
				if ( $value['type'] == 'multi-select' ) {
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

			$data_for_workflow = array(
				'id'   => $post_id,
				'data' => $param,
			);
			do_action( 'ndpvp/webhook', 'lead_add_form', $data_for_workflow );
		}
	}

	public function is_lead_exists( $email, $mobile ) {
		$args = array(
			'post_type'      => array( 'ndpv_person' ),
			'post_status'    => 'publish',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => 'email',
					'value'   => $email,
					'compare' => '=',
				),
			),
			'fields'         => 'ids',
			'posts_per_page' => 1,
		);

		if ( $mobile ) {
			$args['meta_query'][] = array(
				'key'     => 'mobile',
				'value'   => $mobile,
				'compare' => '=',
			);
		}

		$person = get_posts( $args );

		if ( $person ) {
			$person_id = $person[0];
			$args      = array(
				'post_type'      => array( 'ndpv_lead' ),
				'post_status'    => 'publish',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => 'person_id',
						'value'   => $person_id,
						'compare' => '=',
					),
				),
				'fields'         => 'ids',
				'posts_per_page' => 1,
			);

			$lead = get_posts( $args );

			if ( $lead ) {
				$lead_id = $lead[0];
				return $lead_id;
			}
		}
		return false;
	}

	public function total( $id = null ) {
		$args = array(
			'post_type'      => 'ndpv_lead',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);

		$args['meta_query'] = array(
			'relation' => 'OR',
		);

		if ( $id ) {
			$args['meta_query'][] = array(
				array(
					'key'   => 'person_id',
					'value' => $id,
				),
			);

			$args['meta_query'][] = array(
				array(
					'key'   => 'org_id',
					'value' => $id,
				),
			);
		}

		if ( current_user_can( 'ndpv_staff' ) ) {
			$post_ids = Fns::get_posts_ids_by_type( 'ndpv_lead' );
			if ( ! empty( $post_ids ) ) {
				$args['post__in'] = $post_ids;
				$args['orderby']  = 'post__in';
			} else {
				$args['author'] = get_current_user_id();
			}
		}

		$query      = new \WP_Query( $args );
		$total_data = $query->found_posts;
		wp_reset_postdata();
		return $total_data;
	}
}
