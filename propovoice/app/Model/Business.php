<?php

namespace Ndpv\Model;

class Business
{

    public function info()
    {

        $args = [
            "post_type" => "ndpv_business",
            "post_status" => "publish",
            "posts_per_page" => 1,
        ];

        $args["meta_query"] = [
            "relation" => "OR",
        ];

        if ( isset($request["default"]) ) {
            $args["meta_query"][] = [
                [
                    "key" => "default",
                    "value" => 1,
                    "compare" => "LIKE",
                ],
            ];
        }

        $args["meta_query"][] = [
            [
                "key" => "ws_id",
                "value" => ndpv()->get_workspace(),
                "compare" => "LIKE",
            ],
        ];

        $query = new \WP_Query($args);
        $data = [
            'name' => '',
            'email' => ''
        ];
        while ($query->have_posts()) {
            $query->the_post();
            $id = get_the_ID();

            $data["id"] = $id;

            $data["name"] = get_post_meta($id, "name", true);
            $data["email"] = get_post_meta($id, "email", true);
        }
        wp_reset_postdata();

        return $data;
    }
}
