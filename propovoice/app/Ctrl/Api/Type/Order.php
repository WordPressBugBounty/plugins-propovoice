<?php

namespace Ndpv\Ctrl\Api\Type;

use Ndpv\Helper\Fns;
use Ndpv\Model\Org;
use Ndpv\Model\Person;
use Ndpv\Model\Lead as LeadModel;
use Ndpv\Traits\Singleton;

class Order
{
    use Singleton;

    public function register_routes()
    {
        register_rest_route("ndpv/v1", "/orders/(?P<id>\d+)", [
            "methods" => "GET",
            "callback" => [$this, "get_single"],
            "permission_callback" => [$this, "get_per"],
            "args" => [
                "id" => [
                    "validate_callback" => function ($param) {
                        return is_numeric($param);
                    },
                ],
            ],
        ]);

        register_rest_route("ndpv/v1", "/orders" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"],
        ]);

        register_rest_route("ndpv/v1", "/orders", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/orders/(?P<id>\d+)", [
            "methods" => "PUT",
            "callback" => [$this, "update"],
            "permission_callback" => [$this, "update_per"],
            "args" => [
                "id" => [
                    "validate_callback" => function ($param) {
                        return is_numeric($param);
                    },
                ],
            ],
        ]);

        register_rest_route("ndpv/v1", "/orders/(?P<id>[0-9,]+)", [
            "methods" => "DELETE",
            "callback" => [$this, "delete"],
            "permission_callback" => [$this, "del_per"],
            "args" => [
                "id" => [
                    "sanitize_callback" => "sanitize_text_field",
                ],
            ],
        ]);
    }

    public function get($req)
    {
        $param = $req->get_params();

        $per_page = 10;
        $offset = 0;

        $s = isset($param["text"]) ? sanitize_text_field($param["text"]) : null;

        if (isset($param["per_page"])) {
            $per_page = $param["per_page"];
        }

        if (isset($param["page"]) && $param["page"] > 1) {
            $offset = $per_page * $param["page"] - $per_page;
        }

        $args = [
            "post_type" => "ndpv_package_order",
            "post_status" => "publish",
            "posts_per_page" => $per_page,
            "offset" => $offset,
        ];

        $args["meta_query"] = [
            "relation" => "OR",
        ];

        if (current_user_can("ndpv_client_role")) {
            $user_id = get_current_user_id();
            $client_id = get_user_meta($user_id, 'ndpv_client_id', true);

            $args["meta_query"][] = [
                [
                    "key" => "client_id",
                    "value" => [$client_id],
                    "compare" => "IN",
                ],
            ];
        }

        $query = new \WP_Query($args);
        $total_data = $query->found_posts; //use this for pagination
        $result = $data = [];
        while ($query->have_posts()) {
            $query->the_post();
            $id = get_the_ID();

            $query_data = [];
            $query_data["id"] = $id;

            $query_data["package"] = [];

            $query_data["status"] = get_post_meta($id, 'status', true);
            $query_data["payment_method"] = get_post_meta($id, 'payment_method', true);
            $query_data["payment_info"] = get_post_meta($id, 'payment_info', true);

            $packageID = get_post_meta($id, 'package_id', true);
            $packageMeta = get_post_meta($packageID);
            $query_data["package"]["status"] = get_post_status($packageID);
            $query_data["package"]["title"] = get_the_title($packageID);
            $query_data["package"]["desc"] = get_post_field('post_content', $packageID);
            $query_data["package"]['currency'] = isset($packageMeta['currency']) ? $packageMeta['currency'][0] : '';
            $query_data["package"]['price'] = isset($packageMeta['price']) ? $packageMeta['price'][0] : '';
            $query_data["package"]['is_recurring'] = isset($packageMeta['is_recurring']) ? $packageMeta['is_recurring'][0] : false;

            $img_id = isset($packageMeta['img']) ? $packageMeta['img'][0] : '';
            $imgData = null;
            if ($img_id) {
                $img_src = wp_get_attachment_image_src($img_id, 'thumbnail');
                if ($img_src) {
                    $imgData = [];
                    $imgData['id'] = $img_id;
                    $imgData['src'] = $img_src[0];
                }
            }
            $query_data["package"]['img'] = $imgData;

            $query_data["client"] = null;
            $person_id = get_post_meta($id, "client_id", true);
            if ($person_id) {
                $person = new Person();
                $query_data["client"] = $person->single($person_id);
            }

            $query_data["date"] = get_the_time(get_option("date_format"));
            $data[] = $query_data;
        }
        wp_reset_postdata();

        $result["result"] = $data;
        $result["total"] = $total_data;

        wp_send_json_success($result);
    }

    public function get_single($req)
    {
        $url_params = $req->get_url_params();
        $id = $url_params["id"];
        $query_data = [];
        $query_data["id"] = absint($id);

        $queryMeta = get_post_meta($id);
        $query_data["ws_id"] = isset($queryMeta["ws_id"])
            ? $queryMeta["ws_id"][0]
            : "";
        $query_data["tab_id"] = isset($queryMeta["tab_id"])
            ? absint($queryMeta["tab_id"][0])
            : "";
        $query_data["status"] = isset($queryMeta["status"])
            ? sanitize_text_field( $queryMeta["status"][0])
            : "";

        $query_data["max_req"] = Fns::project_request_limit($id, true);
        $query_data["max_req_at_a_time"] = Fns::project_request_limit($id);

        $query_data["package"] = [];

        $packageID = get_post_meta($id, 'package_id', true);
        $packageMeta = get_post_meta($packageID);
        $query_data["package"]["id"] = $packageID;
        $query_data["package"]["title"] = get_the_title($packageID);
        $query_data["package"]["desc"] = get_post_field('post_content', $packageID);
        $query_data["package"]['currency'] = isset($packageMeta['currency']) ? $packageMeta['currency'][0] : '';
        $query_data["package"]['price'] = isset($packageMeta['price']) ? $packageMeta['price'][0] : '';
        $query_data["package"]['max_req'] = isset($packageMeta['max_req']) ? $packageMeta['max_req'][0] : '';
        $query_data["package"]['max_req_at_a_time'] = isset($packageMeta['max_req_at_a_time']) ? $packageMeta['max_req_at_a_time'][0] : '';
        $query_data["package"]['is_recurring'] = isset($packageMeta['is_recurring']) ? $packageMeta['is_recurring'][0] : false;
        $query_data["package"]["recurring"] = isset($packageMeta["recurring"])
                ? maybe_unserialize( $packageMeta["recurring"][0] )
                : null;
        if (!$query_data["package"]["recurring"]) {
            $recurringData = [];
            $recurringData["interval_type"] = "month";
            $recurringData["interval_in"] = "month";
            $recurringData["interval"] = 1;
            $recurringData["limit_type"] = 0;
            $recurringData["limit"] = 5;
            $recurringData["subscription"] = false;
            $recurringData["send_me"] = false;
            $recurringData["delivery"] = 1;
            $query_data["package"]["recurring"] = $recurringData;
        }

        $img_id = isset($packageMeta['img']) ? $packageMeta['img'][0] : '';
        $imgData = null;
        if ($img_id) {
            $img_src = wp_get_attachment_image_src($img_id, 'thumbnail');
            if ($img_src) {
                $imgData = [];
                $imgData['id'] = $img_id;
                $imgData['src'] = $img_src[0];
            }
        }
        $query_data["package"]['img'] = $imgData;

        $query_data["person"] = null;
        $person_id = get_post_meta($id, "client_id", true);
        if ($person_id) {
            $person = new Person();
            $query_data["person"] = $person->single($person_id);
        }

        $query_data["status_id"] = "";
        $status = get_the_terms($id, "ndpv_order_status");
        if ($status) {
            $term_id = $status[0]->term_id;
            $query_data["status_id"] = [
                "id" => $term_id,
                "label" => $status[0]->name,
                "color" => "#4a5568",
                "bg_color" => "#E2E8F0",
                "type" => get_term_meta($term_id, "type", true),
            ];

            $color = get_term_meta($term_id, "color", true);
            $bg_color = get_term_meta($term_id, "bg_color", true);

            if ($color) {
                $query_data["status_id"]["color"] = $color;
            }

            if ($bg_color) {
                $query_data["status_id"]["bg_color"] = $bg_color;
            }
        }

        $query_data["client"] = null;
        $client_id = isset($queryMeta["client_id"])
            ? $queryMeta["client_id"][0]
            : "";
        if ($client_id) {
            $client = new Person();
            $query_data["client"] = $client->single($person_id, true);
        }

        $query_data["date"] = get_the_time(get_option("date_format"), $id);

        wp_send_json_success($query_data);
    }

    public function create($req)
    {
        
    }

    public function update($req)
    {
        
    }

    public function delete($req)
    {
        //TODO: when delete package delete task note file, if not exist in deal project
        $url_params = $req->get_url_params();
        $ids = explode(",", $url_params["id"]);
        foreach ($ids as $id) {
            wp_delete_post($id);
        }

        do_action("ndpvp/webhook", "package_del", $ids);

        wp_send_json_success($ids);
    }

    // check permission
    public function get_per()
    {
        return current_user_can("ndpv_order") || current_user_can("ndpv_client_role");
    }

    public function create_per()
    {
        return current_user_can("ndpv_order");
    }

    public function update_per()
    {
        return current_user_can("ndpv_order");
    }

    public function del_per()
    {
        return current_user_can("ndpv_order");
    }
}
