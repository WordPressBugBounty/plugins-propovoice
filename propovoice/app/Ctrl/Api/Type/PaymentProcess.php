<?php

namespace Ndpv\Ctrl\Api\Type;

use Ndpv\Model\Order;
use Ndpv\Traits\Singleton;

class PaymentProcess
{
    use Singleton;

    public function register_routes()
    {
        register_rest_route("ndpv/v1", "/payment-process" . ndpv()->plain_route(), [
            "methods" => "GET",
            "callback" => [$this, "get"],
            "permission_callback" => [$this, "get_per"]
        ]);

        register_rest_route("ndpv/v1", "/payment-process", [
            "methods" => "POST",
            "callback" => [$this, "create"],
            "permission_callback" => [$this, "create_per"]
        ]);
    }

    public function get($req)
    {
        $request = $req->get_params();
    }

    public function create($req)
    {
        $param = $req->get_params();

        $invoice_id = isset($param["invoice_id"]) ? $param["invoice_id"] : "";
        $type = isset($param["type"]) ? sanitize_text_field( $param["type"] ) : "";
        $param["post_id"] = $invoice_id;
        $param["id"] = $invoice_id;
        $payment_method = isset($param["payment_method"])
            ? $param["payment_method"]
            : "";

        if ($invoice_id) {
            update_post_meta($invoice_id, "payment_method", $payment_method);

            $payment_info = '';

            if ($payment_method == "bank") {

                    $mark_as_paid = isset($param["mark_as_paid"])
                    ? $param["mark_as_paid"]
                    : false;
                    if ($mark_as_paid) {
                        update_post_meta($invoice_id, "status", "paid");
                        if ( $type != 'package' ) {
                            do_action("ndpvp/webhook", "inv_paid", $param);
                        }
                    } else {
                        update_post_meta($invoice_id, "status", "paid_req");
                        if ( $type != 'package' ) {
                            do_action("ndpvp/webhook", "inv_paid_req", $param);
                        }
                    }

                    $payment_details = isset($param["payment_details"])
                        ? $param["payment_details"]
                        : "";
                    $receipt = isset($param["receipt"]) ? $param["receipt"] : "";
                    $note = isset($param["note"]) ? nl2br($param["note"]) : "";

                    $bank_info = [];
                    $bank_info["payment_details"] = $payment_details;
                    $bank_info["receipt"] = $receipt;
                    $bank_info["note"] = $note;
                    $bank_info["date"] = current_time("timestamp");

                    $payment_info = $bank_info;

                    if ( $type != 'package' ) {
                        update_post_meta($invoice_id, "payment_info", $bank_info);
                    }
            } elseif ($payment_method == "paypal" || $payment_method == "stripe") {
                if ( $type != 'package' ) {
                    do_action("ndpvp/webhook", "inv_paid", $param);
                }
                $payment_info = isset($param["payment_info"])
                    ? $param["payment_info"]
                    : "";
                if ( $type != 'package' ) {
                    update_post_meta($invoice_id, "status", "paid");
                    update_post_meta($invoice_id, "payment_info", $payment_info);
                }
            }

            if ( $type == 'package' && !$mark_as_paid) {
                $order = new Order();
                $order_id = $order->create([
                    'package_id' => $invoice_id,
                    'payment_method' => $payment_method,
                    'payment_info' => $payment_info,
                ]);
                $param = [];
                $param['id'] = $order_id;
                do_action("ndpvp/webhook", "order_add", $param);

            }
        }

        wp_send_json_success();
    }

    // check permission
    public function get_per()
    {
        return current_user_can("ndpv_payment");
    }

    public function create_per()
    {
        return true;
    }
}
