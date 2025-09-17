<?php

namespace Ndpv\Api\Type;

use Ndpv\Models\Order;
use Ndpv\Traits\Singleton;

class PaymentProcess {

    use Singleton;

    public function routes() {
        register_rest_route(
            'ndpv/v1',
            '/payment-process' . ndpv()->plain_route(),
            [
                'methods' => 'GET',
                'callback' => [ $this, 'get' ],
                'permission_callback' => [ $this, 'get_per' ],
            ]
        );

        register_rest_route(
            'ndpv/v1',
            '/payment-process',
            [
                'methods' => 'POST',
                'callback' => [ $this, 'create' ],
                'permission_callback' => [ $this, 'create_per' ],
            ]
        );
    }

    public function get( $req ) {
        $request = $req->get_params();
    }

    public function create( $req ) {
        $param = $req->get_params();

        // Sanitize and initialize
        $invoice_id = $param['invoice_id'] ?? '';
        $package_id = $param['package_id'] ?? '';
        $given_token = $param['token'] ?? '';
        $type = sanitize_text_field( $param['type'] ?? '' );
        $mark_as_paid = $param['mark_as_paid'] ?? false;
        $payment_method = $param['payment_method'] ?? '';
        $payment_details = $param['payment_details'] ?? '';
        $receipt = $param['receipt'] ?? '';
        $note = nl2br( $param['note'] ?? '' );

        $is_package = $type === 'package';
        $id = $is_package ? $package_id : $invoice_id;

        // Token check (package or invoice)
        $saved_token = get_post_meta( $id, 'token', true );

        if ( ! $id || ( ! $mark_as_paid && $given_token !== $saved_token ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized or missing ID' ] );
        }

        // Load existing payment info
        $payment_info = get_post_meta( $id, 'payment_info', true );
        $payment_info = is_array( $payment_info ) ? $payment_info : [];

        // Merge new payment info if provided
        if ( ! empty( $param['payment_info'] ) ) {
            $info = $param['payment_info'];
            $info['payment_method'] = $payment_method;
            $info['status'] = ( $payment_method !== 'bank' ) ? 'payment_accepted' : 'payment_requested';

            if ( $payment_method === 'bank' ) {
                $info['note'] = $note;
                $info['date'] = current_time( 'timestamp' );
            }

            $payment_info[] = $info;
        }

        update_post_meta( $id, 'payment_method', $payment_method );

        // Payment handling
        switch ( $payment_method ) {
            case 'bank':
                update_post_meta( $id, 'status', $mark_as_paid ? 'paid' : 'paid_req' );

                if ( ! $is_package ) {
                    do_action( 'ndpvp_webhook', $mark_as_paid ? 'inv_paid' : 'inv_paid_req', $param );
                    update_post_meta( $id, 'payment_info', $payment_info );
                }
                break;

            case 'paypal':
            case 'stripe':
                if ( ! $is_package ) {
                    do_action( 'ndpvp_webhook', 'inv_paid', $param );
                    update_post_meta( $id, 'payment_info', $payment_info );
                }
                break;
        }

        // Package order creation
        if ( $is_package && ! $mark_as_paid ) {
            $order = new Order();
            $order_id = $order->create(
                [
					'package_id' => $package_id,
					'payment_method' => $payment_method,
					'payment_info' => $payment_info,
				]
            );

            do_action( 'ndpvp_webhook', 'order_add', [ 'id' => $order_id ] );
        }

        // Update invoice status
        if ( ! $is_package ) {
            $this->update_invoice_payment_status( $invoice_id );
        }

        wp_send_json_success();
    }

    // public function create($req)
    // {
    //     $param = $req->get_params();
    //     error_log(print_r($param, true));

    //     // Sanitize and initialize
    //     $invoice_id = $param['invoice_id'] ?? '';
    //     $given_token = $param['token'] ?? '';
    //     $type = sanitize_text_field($param['type'] ?? '');
    //     $mark_as_paid = $param['mark_as_paid'] ?? false;
    //     $payment_method = $param['payment_method'] ?? '';
    //     $payment_details = $param['payment_details'] ?? '';
    //     $receipt = $param['receipt'] ?? '';
    //     $note = nl2br($param['note'] ?? '');

    //     $is_package = $type === 'package';
    //     $saved_token = get_post_meta($invoice_id, 'token', true);

    //     // Abort if no permission
    //     if (! $invoice_id || (! $is_package && ! $mark_as_paid && $given_token !== $saved_token)) {
    //         wp_send_json_error();
    //     }

    //     // Prepare payment_info
    //     $payment_info = get_post_meta($invoice_id, 'payment_info', true);
    //     $payment_info = is_array($payment_info) ? $payment_info : [];

    //     if (! empty($param['payment_info'])) {
    //         $info = $param['payment_info'];
    //         $info['payment_method'] = $payment_method;
    //         $info['status'] = ($payment_method !== 'bank') ? 'payment_accepted' : 'payment_requested';

    //         if ($payment_method === 'bank') {
    //             $info['note'] = $note;
    //             $info['date'] = current_time('timestamp');
    //         }

    //         $payment_info[] = $info;
    //     }

    //     update_post_meta($invoice_id, 'payment_method', $payment_method);

    //     // Handle payment method logic
    //     switch ($payment_method) {
    //         case 'bank':
    //             update_post_meta($invoice_id, 'status', $mark_as_paid ? 'paid' : 'paid_req');

    //             if (! $is_package) {
    //                 do_action('ndpvp_webhook', $mark_as_paid ? 'inv_paid' : 'inv_paid_req', $param);
    //                 update_post_meta($invoice_id, 'payment_info', $payment_info);
    //             }
    //             break;

    //         case 'paypal':
    //         case 'stripe':
    //             if (! $is_package) {
    //                 do_action('ndpvp_webhook', 'inv_paid', $param);
    //                 update_post_meta($invoice_id, 'payment_info', $payment_info);
    //             }
    //             break;
    //     }

    //     // If this is a package order and not being marked as paid, create order
    //     if ($is_package && ! $mark_as_paid) {
    //         $order = new Order;
    //         $order_id = $order->create([
    //             'package_id' => $invoice_id,
    //             'payment_method' => $payment_method,
    //             'payment_info' => $payment_info,
    //         ]);

    //         do_action('ndpvp_webhook', 'order_add', ['id' => $order_id]);
    //     }

    //     // Update final invoice status
    //     $this->update_invoice_payment_status($invoice_id);

    //     wp_send_json_success();
    // }

    public function update_invoice_payment_status( $invoice_id ) {
        $total_amount = (float) get_post_meta( $invoice_id, 'total', true );
        $payment_info = maybe_unserialize( get_post_meta( $invoice_id, 'payment_info', true ) );

        $paid_amount = 0;

        if ( is_array( $payment_info ) ) {
            foreach ( $payment_info as $payment ) {
                // Only count payments that were accepted
                if ( isset( $payment['status'] ) && $payment['status'] === 'payment_accepted' ) {
                    $paid_amount += floatval( $payment['amount'] ?? 0 );
                }
            }
        }

        // Decide status
        if ( $paid_amount >= $total_amount && $total_amount > 0 ) {
            $status = 'paid';
        } elseif ( $paid_amount > 0 ) {
            $status = 'partial';
        } else {
            $status = 'unpaid';
        }

        // Store status as a separate post meta
        update_post_meta( $invoice_id, 'status', $status );
        update_post_meta( $invoice_id, 'paid', $paid_amount );
    }

    // public function create($req)
    // {
    //     $param = $req->get_params();

    //     $invoice_id = isset($param['invoice_id']) ? $param['invoice_id'] : '';
    //     $given_token = isset($param['token']) ? $param['token'] : '';
    //     $type = isset($param['type']) ? sanitize_text_field($param['type']) : '';
    //     $param['post_id'] = $invoice_id;
    //     $param['id'] = $invoice_id;
    //     $mark_as_paid = isset($param['mark_as_paid'])
    //         ? $param['mark_as_paid']
    //         : false;
    //     $payment_method = isset($param['payment_method'])
    //         ? $param['payment_method']
    //         : '';
    //     $payment_details = isset($param['payment_details'])
    //         ? $param['payment_details']
    //         : '';
    //     $payment_info = isset($param['payment_info'])
    //         ? $param['payment_info']
    //         : '';

    //     $saved_token = get_post_meta($invoice_id, 'token', true);

    //     if ($invoice_id && (($type !== 'package' && $given_token == $saved_token) || $type == 'package' || $mark_as_paid)) {
    //         update_post_meta($invoice_id, 'payment_method', $payment_method);

    //         // $payment_info = '';

    //         if ($payment_method === 'bank') {
    //             if ($mark_as_paid) {
    //                 update_post_meta($invoice_id, 'status', 'paid');
    //                 if ($type !== 'package') {
    //                     do_action('ndpvp_webhook', 'inv_paid', $param);
    //                 }
    //             } else {
    //                 update_post_meta($invoice_id, 'status', 'paid_req');
    //                 if ($type !== 'package') {
    //                     do_action('ndpvp_webhook', 'inv_paid_req', $param);
    //                 }
    //             }

    //             $receipt = isset($param['receipt']) ? $param['receipt'] : '';
    //             $note = isset($param['note']) ? nl2br($param['note']) : '';

    //             $bank_info = [];
    //             $bank_info['payment_details'] = $payment_details;
    //             $bank_info['receipt'] = $receipt;
    //             $bank_info['note'] = $note;
    //             $bank_info['date'] = current_time('timestamp');

    //             $payment_info = $bank_info;

    //             if ($type !== 'package') {
    //                 update_post_meta($invoice_id, 'payment_info', $bank_info);
    //             }
    //         } elseif ($payment_method === 'paypal' || $payment_method === 'stripe') {
    //             if ($type !== 'package') {
    //                 do_action('ndpvp_webhook', 'inv_paid', $param);
    //             }
    //             if ($type !== 'package') {
    //                 update_post_meta($invoice_id, 'status', 'paid');
    //                 update_post_meta($invoice_id, 'payment_info', $payment_info);
    //             }
    //         }

    //         if ($type === 'package' && ! $mark_as_paid) {
    //             $order = new Order;
    //             $order_id = $order->create(
    //                 [
    //                     'package_id' => $invoice_id,
    //                     'payment_method' => $payment_method,
    //                     'payment_info' => $payment_info,
    //                 ]
    //             );
    //             $param = [];
    //             $param['id'] = $order_id;
    //             do_action('ndpvp_webhook', 'order_add', $param);
    //         }
    //     } else {
    //         wp_send_json_error();
    //     }

    //     wp_send_json_success();
    // }

    // check permission
    public function get_per() {
        return current_user_can( 'ndpv_payment' );
    }

    public function create_per() {
        return true;
    }
}
