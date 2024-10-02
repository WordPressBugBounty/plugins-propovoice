<?php
namespace Ndpv\Models;

class Contact {

    public function query( $s, $type ) {

        $per_page = 10;
        $offset = 0;

        $args = [
            'post_type' => 'ndpv_' . $type,
            'post_status' => 'publish',
            'fields' => 'ids',
            'posts_per_page' => $per_page,
            'offset' => $offset,
        ];

        $args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            'relation' => 'OR',
        ];

        if ( $s ) {
            if ( $type === 'person' ) {
                $args['meta_query'][] = [
                    [
                        'key'     => 'first_name',
                        'value'   => $s,
                        'compare' => 'Like',
                    ],
                ];
            } else {
                $args['meta_query'][] = [
                    [
                        'key'     => 'name',
                        'value'   => $s,
                        'compare' => 'Like',
                    ],
                ];
            }

            $args['meta_query'][] = [
                [
                    'key'     => 'email',
                    'value'   => $s,
                    'compare' => 'Like',
                ],
            ];
        }

        $query = new \WP_Query( $args );
        return $query->posts;
    }
}
