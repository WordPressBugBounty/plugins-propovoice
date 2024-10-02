<?php

namespace Ndpv\Ctrl\Api\Type;

use Ndpv\Helper\Fns;
use Ndpv\Traits\Singleton;

class Package
{
    use Singleton;

    public function register_routes()
    {
        register_rest_route("ndpv/v1", "/packages/(?P<id>\d+)", [
            "methods" => "GET",
            "callback" => [$this, "get_single"],
            "permission_callback" => [$this, "get_per_single"],
            "args" => [
                "id" => [
                    "validate_callback" => function ($param) {
                        return is_numeric($param);
                    },
                ],
            ],
        ]);

        register_rest_route("ndpv/v1", "/packages" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"]
        ]);

        register_rest_route("ndpv/v1", "/packages", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);

        register_rest_route("ndpv/v1", "/packages/(?P<id>\d+)", [
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

        register_rest_route("ndpv/v1", "/packages/(?P<id>[0-9,]+)", [
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
        //this is the short solution to fix plain permalink issue
        $permalink_structure = get_option('permalink_structure');
        if ($permalink_structure === '' && isset($param['token'])) {
            $text = isset($param['args']) ? $param['args'] : '';

            preg_match('/\d+/', $text, $matches);
            if (!empty($matches)) {
                $req->set_url_params(['id' => $matches[0]]);
                $this->get_single($req);
            }
            return;
        }

        $per_page = 10;
        $offset = 0;

        $s = isset($param["text"]) ? sanitize_text_field($param["text"]) : null;
        $module_id = isset($param["module_id"])
            ? absint($param["module_id"])
            : null;

        if (isset($param["per_page"])) {
            $per_page = $param["per_page"];
        }

        if (isset($param["page"]) && $param["page"] > 1) {
            $offset = $per_page * $param["page"] - $per_page;
        }

        $args = [
            "post_type" => "ndpv_package",
            "post_status" => "publish",
            "posts_per_page" => $per_page,
            "offset" => $offset,
            "orderby" => "ID",
            "order" => "DESC"
        ];

        $args["meta_query"] = [
            "relation" => "AND",
        ];

        if ( current_user_can("ndpv_client_role") ) {
            $term_id = Fns::get_term_id_by_type('package_status', 'active' );
            $args["tax_query"] = [
                [
                    "taxonomy" => "ndpv_package_status",
                    "terms" => $term_id,
                    "field" => "term_id",
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

            $packageMeta = get_post_meta($id);
            $query_data["title"] = get_the_title($id);
            $query_data["desc"] = get_post_field('post_content', $id);
            $query_data['currency'] = isset($packageMeta['currency']) ? $packageMeta['currency'][0] : '';
            $query_data['price'] = isset($packageMeta['price']) ? $packageMeta['price'][0] : '';
            $query_data['is_recurring'] = isset($packageMeta['is_recurring']) ? $packageMeta['is_recurring'][0] : false;

            $query_data["status_id"] = "";
            $status = get_the_terms($id, "ndpv_package_status");
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
            $query_data['img'] = $imgData;

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
        $param = $req->get_params();

        $url_params = $req->get_url_params();

        $id = absint($url_params["id"]);
        $query_data = $package = [];

        if ($id) {
            $query_data["id"] = $id;

            //edit
            $package["id"] = $id;

            $packageMeta = get_post_meta($id);
            $package["status"] = get_post_status($id);
            $package["title"] = get_the_title($id);
            $package["desc"] = get_post_field('post_content', $id);
            $package["token"] = isset($packageMeta["token"]) ? $packageMeta["token"][0] : "";
            $package['currency'] = isset($packageMeta['currency']) ? $packageMeta['currency'][0] : '';
            $package['total'] = isset($packageMeta['price']) ? $packageMeta['price'][0] : '';
            $package['price'] = isset($packageMeta['price']) ? $packageMeta['price'][0] : '';
            $package['max_req'] = isset($packageMeta['max_req']) ? $packageMeta['max_req'][0] : '';
            $package['max_req_at_a_time'] = isset($packageMeta['max_req_at_a_time']) ? $packageMeta['max_req_at_a_time'][0] : '';

            $package['is_reminder'] = isset($packageMeta['is_reminder']) ? $packageMeta['is_reminder'][0] : false;
            $reminder = isset($packageMeta["reminder"])
                ? $packageMeta["reminder"]
                : null;
            if (!$reminder) {
                $reminderData = [];
                $reminderData["due_date"] = false;
                $reminderData["before"] = [];
                $reminderData["after"] = [15];
                $package["reminder"] = $reminderData;
            }

            $package['is_recurring'] = isset($packageMeta['is_recurring']) ? $packageMeta['is_recurring'][0] : false;
            $package["recurring"] = isset($packageMeta["recurring"])
                ? maybe_unserialize( $packageMeta["recurring"][0] )
                : null;

            if ( $package['is_recurring'] ) {
                $package["recurring"]['status'] = true;
                $package["recurring"]['subscription'] = true;
            }

            if (!$package["recurring"] && !isset($param["client_view"])) {
                $recurringData = [];
                $recurringData["interval_type"] = "month";
                $recurringData["interval_in"] = "month";
                $recurringData["interval"] = 1;
                $recurringData["limit_type"] = 0;
                $recurringData["limit"] = 5;
                $recurringData["subscription"] = false;
                $recurringData["send_me"] = false;
                $recurringData["delivery"] = 1;
                $package["recurring"] = $recurringData;
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
            $package['img'] = $imgData;

            if (isset($param["client_view"])) {
                /* $token = isset($param["token"])
                    ? sanitize_text_field($param["token"])
                    : "";
                $post_token = get_post_meta($id, "token", true);

                $is_admin =
                    is_user_logged_in() &&
                    apply_filters(
                        "ndpv_admin",
                        current_user_can("administrator")
                    );

                $auth = false;
                if ($is_admin || $token == $post_token) {
                    $auth = true;
                }

                if (!$auth) {
                    wp_send_json_error();
                } */
                $recurring = $package['is_recurring'];
                if (
                    $recurring &&
                    (!$recurring["status"] || !$recurring["subscription"])
                ) {
                    /* unset($invoice["recurring"]);
                    $invoice["recurring"]['status'] = false;
                    $invoice["recurring"]['subscription'] = false; */
                }

                $package["payment_methods"] =  [];
                $payment_methods = null;
                $option = get_option("ndpv_package_payment");
                if ($option && $option['payment_methods'] ) {
                    $payment_methods = $option['payment_methods'];
                }
                if ($payment_methods) {
                    $new_payment_methods = [];

                    foreach ($payment_methods as $key => $payment_id) {
                        $payment_query_data = [];
                        $payment_query_data["id"] = $payment_id;

                        if ($key == "bank") {
                            $payment_query_data["name"] = get_post_meta(
                                $payment_id,
                                "name",
                                true
                            );
                            $payment_query_data["details"] = get_post_meta(
                                $payment_id,
                                "details",
                                true
                            );
                        } elseif ($key == "paypal") {
                            $payment_query_data["account_type"] = get_post_meta(
                                $payment_id,
                                "account_type",
                                true
                            );
                            $payment_query_data["client_id"] = get_post_meta(
                                $payment_id,
                                "client_id",
                                true
                            );
                        } elseif ($key == "stripe") {
                            $payment_query_data["public_key"] = get_post_meta(
                                $payment_id,
                                "public_key",
                                true
                            );
                        }

                        $new_payment_methods[$key] = $payment_query_data;
                    }

                    $package["payment_methods"] = $new_payment_methods;
                }
            }
        }

        $query_data["wc"] = false;
        if (ndpv()->wage()) {
            $wc = get_option("ndpv_payment_wc");
            if (isset($wc["status"])) {
                if ($wc["status"] && class_exists("woocommerce")) {
                    $query_data["wc"] = true;
                }
            }
        }

        $query_data["packageData"] = $package;

        wp_send_json_success($query_data);
    }

    public function create($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();
        $is_recurring = isset($param["is_recurring"]) ? rest_sanitize_boolean( $param["is_recurring"] ) : false;

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            //TODO: give proper title
            $title = "";
            $data = [
                "post_type" => "ndpv_package",
                "post_title" => $title,
                "post_status" => "publish",
                "post_author" => get_current_user_id(),
            ];
            $post_id = wp_insert_post($data);

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, "ws_id", ndpv()->get_workspace());

                update_post_meta($post_id, "is_recurring", $is_recurring);

                $term_id = Fns::get_term_id_by_type('package_status', 'draft' );
                if ( $term_id ) {
                    wp_set_post_terms(
                        $post_id,
                        [$term_id],
                        "ndpv_package_status"
                    );
                }

                //generate secret token
                $bytes = random_bytes(20);
                $token = bin2hex($bytes);
                update_post_meta($post_id, "token", $token);

                // do_action("ndpvp/webhook", $hook . "_add", $param);

                wp_send_json_success([
                    "id" => $post_id
                ]);
            } else {
                wp_send_json_error();
            }
        }
    }

    public function update($req)
    {
        $param = $req->get_params();
        $reg_errors = new \WP_Error();

        $title = isset($param["title"]) ? sanitize_text_field( $param["title"] ) : '';
        $desc = isset($param["desc"]) ? ( $param["desc"] ) : '';
        $img = isset($param["img"]) ? absint( $param["img"] ) : null;

        $currency = isset($param["currency"]) ? sanitize_text_field( $param["currency"] ) : '';
        $price = isset($param["price"]) ? sanitize_text_field( $param["price"] ) : '';
        $max_req = isset($param["max_req"]) ? absint( $param["max_req"] ) : '';
        $max_req_at_a_time = isset($param["max_req_at_a_time"]) ? absint( $param["max_req_at_a_time"] ) : '';

        $is_recurring = isset($param["is_recurring"]) ? rest_sanitize_boolean( $param["is_recurring"] ) : false;
        $is_reminder = isset($param["is_reminder"]) ? rest_sanitize_boolean( $param["is_reminder"] ) : false;
        $publish = isset($param["publish"]) ? rest_sanitize_boolean( $param["publish"] ) : false;
        $reminder = isset($param["reminder"]) ? $param["reminder"] : null;
        $recurring = isset($param["recurring"]) ? $param["recurring"] : null;

        if ( $publish ) {
            $payment_methods = null;
            $option = get_option("ndpv_package_payment");
            if ($option && $option['payment_methods'] ) {
                $payment_methods = $option['payment_methods'];
            }

            if (!$payment_methods) {
                $reg_errors->add(
                    "package_payment_method",
                    __("Before publish please activate payment method from Setting -> Service", "propovoice")
                );
            }
        }

        if ($reg_errors->get_error_messages()) {
            wp_send_json_error($reg_errors->get_error_messages());
        } else {
            $url_params = $req->get_url_params();
            $post_id = $url_params["id"];

            $data = [
                "ID" => $post_id,
                "post_title" => $title,
                "post_content" => $desc
            ];
            $post_id = wp_update_post($data);

            if (!is_wp_error($post_id)) {

                update_post_meta($post_id, "img", $img);
                update_post_meta($post_id, "currency", $currency);
                update_post_meta($post_id, "price", $price);
                update_post_meta($post_id, "max_req", $max_req);
                update_post_meta($post_id, "max_req_at_a_time", $max_req_at_a_time);
                update_post_meta($post_id, "is_recurring", $is_recurring);
                update_post_meta($post_id, "is_reminder", $is_reminder);

                if ( $publish ) {
                    $term_id = Fns::get_term_id_by_type('package_status', 'active' );
                    if ( $term_id ) {
                        wp_set_post_terms(
                            $post_id,
                            [$term_id],
                            "ndpv_package_status"
                        );
                    }
                }

                if ($reminder) {
                    update_post_meta($post_id, "reminder", $reminder);
                }

                if ($recurring) {
                    update_post_meta($post_id, "recurring", $recurring);
                    update_post_meta(
                        $post_id,
                        "subscription",
                        $recurring["subscription"]
                    );
                }
                // do_action("ndpvp/webhook", $hook . "_edit", $param);

                wp_send_json_success($post_id);
            } else {
                wp_send_json_error();
            }
        }
    }

    public function delete($req)
    {
        $url_params = $req->get_url_params();

        $ids = explode(",", $url_params["id"]);
        foreach ($ids as $id) {
            wp_delete_post($id);
        }

        do_action("ndpvp/webhook", "inv_del", $ids);

        wp_send_json_success($ids);
    }

    // check permission
    public function get_per()
    {
        return current_user_can("ndpv_package");
    }

    public function get_per_single()
    {
        return true;
    }

    public function create_per()
    {
        return current_user_can("ndpv_package");
    }

    public function update_per()
    {
        return current_user_can("ndpv_package");
    }

    public function del_per()
    {
        return current_user_can("ndpv_package");
    }

    public function is_client($id){
        return get_post_meta($id, "is_client", true);
    }
}
