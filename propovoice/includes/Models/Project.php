<?php
namespace Ndpv\Models;

use Ndpv\Helpers\Fns;

class Project {

    public function total( $id = null ) {

        $args = [
            'post_type' => 'ndpv_project',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ];

        if ( ! $id ) {
            if ( current_user_can( 'ndpv_client_role' ) ) {
                $user_id = get_current_user_id();
                $id = get_user_meta( $user_id, 'ndpv_client_id', true );
            }
        }

        if ( $id ) {
            $args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                'relation' => 'OR',
            ];
            $args['meta_query'][] = [
                [
                    'key'   => 'person_id',
                    'value' => $id,
                ],
            ];

            $args['meta_query'][] = [
                [
                    'key'   => 'org_id',
                    'value' => $id,
                ],
            ];
        }

        if ( current_user_can( 'ndpv_staff' ) ) {
            $post_ids = Fns::get_posts_ids_by_type( 'ndpv_project' );
            if ( ! empty( $post_ids ) ) {
                $args['post__in'] = $post_ids;
                $args['orderby'] = 'post__in';
            } else {
                $args['author'] = get_current_user_id();
            }
        }

        $query = new \WP_Query( $args );
        $total_data = $query->found_posts;
        wp_reset_postdata();

        return $total_data;
    }
}
