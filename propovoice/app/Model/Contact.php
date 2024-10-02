<?php 
namespace Ndpv\Model;

class Contact
{

    public function query($s, $type) { 

        $per_page = 10;
        $offset = 0;  

        $args = array(
            'post_type' => 'ndpv_' . $type,
            'post_status' => 'publish',
            'fields' => 'ids',
            'posts_per_page' => $per_page,
            'offset' => $offset,
        );

        $args['meta_query'] = array(
            'relation' => 'OR'
        );

        if ( $s ) {
            if ( $type == 'person' ) {
                $args['meta_query'][] = array(
                    array(
                        'key'     => 'first_name',
                        'value'   => $s,
                        'compare' => 'Like',
                    )
                ); 
            } else {
                $args['meta_query'][] = array(
                    array(
                        'key'     => 'name',
                        'value'   => $s,
                        'compare' => 'Like',
                    )
                ); 
            }

            $args['meta_query'][] = array(
                array(
                    'key'     => 'email',
                    'value'   => $s,
                    'compare' => 'Like',
                )
            ); 
        } 

        $query = new \WP_Query($args); 
        return $query->posts;
    } 
}
