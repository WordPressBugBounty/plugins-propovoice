<?php

namespace Ndpv\Model;

use Ndpv\Helper\Fns;

class Client
{

    public function total($id = null)
    {

        $args = array(
            'post_type' => ['ndpv_person', 'ndpv_org'],
            'post_status' => 'publish',
            'posts_per_page' => -1
        );

        $args['meta_query'] = array(
            'relation' => 'OR'
        );

        $args['meta_query'][] = array(
            array(
                'key'     => 'is_client',
                'value'   => 1,
                'compare' => '=',
            )
        );

        if (current_user_can("ndpv_staff")) {
            $post_ids = Fns::get_posts_ids_by_type(['ndpv_person', 'ndpv_org']);
            if (!empty($post_ids)) {
                $args['post__in'] = $post_ids;
                $args['orderby'] = 'post__in';
            } else {
                $args['author'] = get_current_user_id();
            }
        }

        $query = new \WP_Query($args);
        $total_data = $query->found_posts;
        wp_reset_postdata();

        return $total_data;
    }

    public function set_user_if_not($post_id, $name = '', $email = '', $client_portal = false)
    {
        $user_id = email_exists($email);

        if ($client_portal) {
            if (!$user_id) {
                $password = wp_generate_password($length = 12, $include_standard_special_chars = false);
                $user_args = array(
                    'user_login'   => $email,
                    'user_pass'    => $password,
                    'user_email'   => $email,
                    'first_name'   => $name,
                    'nickname'     => $name,
                    'display_name' => $name
                );
                $user_id = wp_insert_user($user_args);
                $user_id_role = new \WP_User($user_id);
                $user_id_role->set_role('ndpv_client_role');

                $type = get_post_type($post_id) == "ndpv_person" ? "person" : "org";
                update_user_meta($user_id, 'ndpv_client_id', $post_id);
                update_user_meta($user_id, 'ndpv_client_type', $type);

                Fns::password_mail($name, $email, $password);
            } else {
                Fns::password_mail($name, $email, 'Use your old password');
            }
        }

        update_user_meta($user_id, 'ndpv_client_portal', $client_portal);

        return $user_id;
    }

    public function delete_client($post_id)
    {

        $type = get_post_type($post_id) == "ndpv_person" ? "person" : "org";

        $meta_queries = array(
            'relation' => 'AND', // Use 'AND' relation for multiple meta queries
            array(
                'key'     => 'ndpv_client_id',
                'value'   => $post_id,
                'compare' => '='
            ),
            array(
                'key'     => 'ndpv_client_type',
                'value'   => $type,
                'compare' => '='
            )
        );

        // Query users based on the multiple meta keys and values
        $user_query = new \WP_User_Query(array(
            'meta_query' => $meta_queries,
        ));

        // Get the results
        $users = $user_query->get_results();

        // Loop through the users
        if (!empty($users)) {
            require_once(ABSPATH . 'wp-admin/includes/user.php');
            foreach ($users as $user) {
                wp_delete_user($user->ID);
            }
        }

        return true;
    }
}
