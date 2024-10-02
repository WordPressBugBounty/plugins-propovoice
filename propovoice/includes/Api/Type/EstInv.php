<?php

namespace Ndpv\Api\Type;

use Ndpv\Helpers\Fns;
use Ndpv\Models\Invoice as ModelInvoice;
use Ndpv\Models\Contact;
use Ndpv\Traits\Singleton;

class EstInv
{

  use Singleton;

  public function routes()
  {
    register_rest_route(
      'ndpv/v1',
      '/invoices/(?P<id>\d+)',
      [
        'methods' => 'GET',
        'callback' => [$this, 'get_single'],
        'permission_callback' => [$this, 'get_per_single'],
        'args' => [
          'id' => [
            'validate_callback' => function ($param) {
              return is_numeric($param);
            },
          ],
        ],
      ]
    );

    register_rest_route(
      'ndpv/v1',
      '/invoices' . ndpv()->plain_route(),
      [
        'methods' => 'GET',
        'callback' => [$this, 'get'],
        'permission_callback' => [$this, 'get_per'],
      ]
    );

    register_rest_route(
      'ndpv/v1',
      '/invoices',
      [
        'methods' => 'POST',
        'callback' => [$this, 'create'],
        'permission_callback' => [$this, 'create_per'],
      ]
    );

    register_rest_route(
      'ndpv/v1',
      '/invoices/(?P<id>\d+)',
      [
        'methods' => 'PUT',
        'callback' => [$this, 'update'],
        'permission_callback' => [$this, 'update_per'],
        'args' => [
          'id' => [
            'validate_callback' => function ($param) {
              return is_numeric($param);
            },
          ],
        ],
      ]
    );

    register_rest_route(
      'ndpv/v1',
      '/invoices/(?P<id>[0-9,]+)',
      [
        'methods' => 'DELETE',
        'callback' => [$this, 'delete'],
        'permission_callback' => [$this, 'del_per'],
        'args' => [
          'id' => [
            'sanitize_callback' => 'sanitize_text_field',
          ],
        ],
      ]
    );
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

    $s = isset($param['text']) ? sanitize_text_field($param['text']) : null;
    $module_id = isset($param['module_id'])
      ? absint($param['module_id'])
      : null;
    $dashboard = isset($param['dashboard']) ? true : false;
    $recurring = isset($param['recurring']) ? true : false;

    if (isset($param['per_page'])) {
      $per_page = $param['per_page'];
    }

    if (isset($param['page']) && $param['page'] > 1) {
      $offset = $per_page * $param['page'] - $per_page;
    }

    if ($dashboard) {
      $per_page = 5;
    }

    $args = [
      'post_type' => 'ndpv_estinv',
      'post_status' => 'publish',
      'posts_per_page' => $per_page,
      'offset' => $offset,
      'orderby' => 'ID',
      'order' => 'DESC',
    ];

    $args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
      'relation' => 'AND',
    ];

    if (current_user_can('ndpv_client_role')) {
      $user_id = get_current_user_id();
      $client_id = get_user_meta($user_id, 'ndpv_client_id', true);


      if ($client_id) {
        $args['meta_query'][] = [
          [
            'key' => 'to',
            'value' => [$client_id],
            'compare' => 'IN',
          ],
        ];
      }
    }

    if ($dashboard) {
      $args['meta_query'][] = [
        [
          'key' => 'status',
          'value' => ['accept', 'decline', 'paid'],
          'compare' => 'NOT IN',
        ],
      ];
    }

    if ($recurring) {
      $args['meta_query'][] = [
        [
          'key' => 'recurring',
          'value' => 1,
        ],
      ];
    }

    if ($s) {
      $contact_person = new Contact();
      $person_ids = $contact_person->query($s, 'person');

      if ($person_ids) {
        $args['meta_query'][] = [
          [
            'key' => 'to',
            'value' => $person_ids,
            'compare' => 'IN',
          ],
        ];
      }

      $org_ids = $contact_person->query($s, 'org');
      if ($org_ids) {
        $args['meta_query'][] = [
          [
            'key' => 'to',
            'value' => $org_ids,
            'compare' => 'IN',
          ],
        ];
      }
    }

    if (isset($param['path'])) {
      $args['meta_query'][] = [
        [
          'key' => 'path',
          'value' => $param['path'],
        ],
      ];
    }

    /* if ( $s ) {
            $args['meta_query'][] = array(
                array(
                    'key'   => 'num',
                    'value' => $s
                )
            );
        } */

    if ($module_id) {
      //if query from client module search key as 'to'
      $module_key = isset($param['client_mod']) ? 'to' : 'module_id';
      $args['meta_query'][] = [
        [
          'key' => $module_key,
          'value' => $module_id,
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
      $query_data['id'] = $id;

      $inv_meta = get_post_meta($id);
      $query_data['num'] = isset($inv_meta['num'])
        ? $inv_meta['num'][0]
        : '';
      $query_data['token'] = isset($inv_meta['token'])
        ? $inv_meta['token'][0]
        : '';
      $query_data['path'] = isset($inv_meta['path'])
        ? $inv_meta['path'][0]
        : '';
      $query_data['date'] = isset($inv_meta['date'])
        ? $inv_meta['date'][0]
        : '';
      $query_data['due_date'] = isset($inv_meta['due_date'])
        ? $inv_meta['due_date'][0]
        : '';

      $query_data['project'] = [
        'name' => '',
      ];

      $from_id = get_post_meta($id, 'from', true);
      $from_data = [];
      if ($from_id) {
        $from_data['id'] = $from_id;
        $from_data['name'] = get_post_meta($from_id, 'name', true);
      }
      $query_data['from'] = $from_data;

      $contact_id = get_post_meta($id, 'to', true);
      $to_type = get_post_meta($id, 'to_type', true);
      $contact_data = [];

      if ($contact_id) {
        $contact_data['id'] = absint($contact_id);
        $contact_data['type'] = $to_type;
        $contact_meta = get_post_meta($contact_id);
        $contact_data['first_name'] = isset($contact_meta['first_name'])
          ? $contact_meta['first_name'][0]
          : '';
        $contact_data['org_name'] = isset($contact_meta['name'])
          ? $contact_meta['name'][0]
          : '';
        $contact_data['email'] = isset($contact_meta['email'])
          ? $contact_meta['email'][0]
          : '';
      }
      $query_data['to'] = $contact_data;

      $query_data['invoice'] = isset($inv_meta['invoice'])
        ? maybe_unserialize($inv_meta['invoice'][0])
        : '';
      $query_data['total'] = isset($inv_meta['total'])
        ? $inv_meta['total'][0]
        : '';

      if (!$query_data['total']) {
        $query_data['total'] = 0;
      }

      $query_data['paid'] = isset($inv_meta['paid'])
        ? $inv_meta['paid'][0]
        : '';
      if (!$query_data['paid']) {
        $query_data['paid'] = 0;
      }

      $query_data['due'] = isset($inv_meta['due'])
        ? $inv_meta['due'][0]
        : '';
      if (!$query_data['due']) {
        $query_data['due'] = 0;
      }

      $query_data['feedback'] = isset($inv_meta['feedback'])
        ? maybe_unserialize($inv_meta['feedback'][0])
        : '';
      $query_data['payment_method'] = isset($inv_meta['payment_method'])
        ? $inv_meta['payment_method'][0]
        : '';
      $query_data['payment_info'] = isset($inv_meta['payment_info'])
        ? maybe_unserialize($inv_meta['payment_info'][0])
        : '';
      $query_data['status'] = isset($inv_meta['status'])
        ? $inv_meta['status'][0]
        : '';

      // $query_data['date'] = get_the_time( get_option('date_format') );
      $data[] = $query_data;
    }
    wp_reset_postdata();

    $path = $param['path'];
    $prefix = get_option('ndpv_' . $path . '_general');
    if ($prefix) {
      $result['prefix'] = $prefix['prefix'];
    } else {
      $result['prefix'] = ($path === 'invoice') ? 'Inv-' : 'Est-';
    }

    $result['result'] = $data;
    $result['total'] = $total_data;

    wp_send_json_success($result);
  }

  public function get_single($req)
  {
    $param = $req->get_params();

    $url_params = $req->get_url_params();

    $id = absint($url_params['id']);
    $query_data = $invoice = [];

    $subs_ref_id = get_post_meta($id, 'subs_ref_id', true);
    if ($subs_ref_id) {
      $id = $subs_ref_id;
    }

    if ($id) {
      //edit
      $query_data['id'] = $id;

      $inv_meta = get_post_meta($id);
      $query_data['token'] = isset($inv_meta['token'])
        ? $inv_meta['token'][0]
        : '';
      $query_data['date'] = isset($inv_meta['date'])
        ? $inv_meta['date'][0]
        : '';
      $query_data['due_date'] = isset($inv_meta['due_date'])
        ? $inv_meta['due_date'][0]
        : '';
      $query_data['module_id'] = isset($inv_meta['module_id'])
        ? $inv_meta['module_id'][0]
        : '';

      $from_id = isset($inv_meta['from']) ? $inv_meta['from'][0] : '';

      $query_data['status'] = isset($inv_meta['status'])
        ? $inv_meta['status'][0]
        : '';
      $from_data = [];

      if ($from_id) {
        $from_data['id'] = $from_id;
        $from_meta = get_post_meta($from_id);
        $from_data['name'] = isset($from_meta['name'])
          ? $from_meta['name'][0]
          : '';
        $from_data['org_name'] = isset($from_meta['org_name'])
          ? $from_meta['org_name'][0]
          : '';
        $from_data['email'] = isset($from_meta['email'])
          ? $from_meta['email'][0]
          : '';
        $from_data['web'] = isset($from_meta['web'])
          ? $from_meta['web'][0]
          : '';
        $from_data['mobile'] = isset($from_meta['mobile'])
          ? $from_meta['mobile'][0]
          : '';
        $from_data['country'] = isset($from_meta['country'])
          ? $from_meta['country'][0]
          : '';
        $from_data['region'] = isset($from_meta['region'])
          ? $from_meta['region'][0]
          : '';
        $from_data['address'] = isset($from_meta['address'])
          ? $from_meta['address'][0]
          : '';
        $from_data['city'] = isset($from_meta['city'])
          ? $from_meta['city'][0]
          : '';
        $from_data['zip'] = isset($from_meta['zip'])
          ? $from_meta['zip'][0]
          : '';
        $logo_id = get_post_meta($from_id, 'logo', true);
        $logo_data = null;
        if ($logo_id) {
          $logo_data = [];
          $logo_data['id'] = $logo_id;
          $logo_src = wp_get_attachment_image_src(
            $logo_id,
            'thumbnail'
          );
          $logo_data['src'] = $logo_src[0];
        }
        $from_data['logo'] = $logo_data;
      }
      $query_data['fromData'] = $from_data;

      $to_id = isset($inv_meta['to']) ? $inv_meta['to'][0] : '';
      $to_type = isset($inv_meta['to_type']) ? $inv_meta['to_type'][0] : '';

      $to_data = [];

      if ($to_id) {
        $to_data['id'] = absint($to_id);
        $to_meta = get_post_meta($to_id);
        $to_data['type'] = $to_type;
        $to_data['first_name'] = isset($to_meta['first_name'])
          ? $to_meta['first_name'][0]
          : '';
        $to_data['org_name'] = isset($to_meta['name'])
          ? $to_meta['name'][0]
          : '';
        $to_data['email'] = isset($to_meta['email'])
          ? $to_meta['email'][0]
          : '';
        $to_data['mobile'] = isset($to_meta['mobile'])
          ? $to_meta['mobile'][0]
          : '';
        $to_data['web'] = isset($to_meta['web']) ? $to_meta['web'][0] : '';
        $to_data['country'] = isset($to_meta['country'])
          ? $to_meta['country'][0]
          : '';
        $to_data['region'] = isset($to_meta['region'])
          ? $to_meta['region'][0]
          : '';
        $to_data['address'] = isset($to_meta['address'])
          ? $to_meta['address'][0]
          : '';
      }
      $query_data['toData'] = $to_data;

      $invoice = isset($inv_meta['invoice'])
        ? maybe_unserialize($inv_meta['invoice'][0])
        : '';

      $reminder = isset($invoice['reminder'])
        ? $invoice['reminder']
        : null;
      if (!$reminder) {
        $reminder_data = [];
        $reminder_data['status'] = false;
        $reminder_data['due_date'] = false;
        $reminder_data['before'] = [];
        $reminder_data['after'] = [15];
        $invoice['reminder'] = $reminder_data;
      }

      $recurring = isset($invoice['recurring'])
        ? $invoice['recurring']
        : null;
      if (!$recurring && !isset($param['client_view'])) {
        $recurring_data = [];
        $recurring_data['status'] = false;
        $recurring_data['interval_type'] = 'week';
        $recurring_data['interval_in'] = 'month';
        $recurring_data['interval'] = 1;
        $recurring_data['limit_type'] = 0;
        $recurring_data['limit'] = 5;
        $recurring_data['subscription'] = false;
        $recurring_data['send_me'] = false;
        $recurring_data['delivery'] = 1;
        $invoice['recurring'] = $recurring_data;
      }

      $payment_data = null;
      if (isset($invoice['payment_methods']['bank'])) {
        $payment_data['id'] = $invoice['payment_methods']['bank'];
        $payment_meta = get_post_meta(
          $invoice['payment_methods']['bank']
        );
        $payment_data['name'] = isset($payment_meta['name'])
          ? $payment_meta['name'][0]
          : '';
        $payment_data['details'] = isset($payment_meta['details'])
          ? $payment_meta['details'][0]
          : '';
      }
      $query_data['paymentBankData'] = $payment_data;

      if (isset($param['client_view'])) {
        $token = isset($param['token'])
          ? sanitize_text_field($param['token'])
          : '';
        $post_token = get_post_meta($id, 'token', true);

        $is_admin =
          is_user_logged_in() &&
          apply_filters(
            'ndpv_admin',
            current_user_can('manage_options')
          );

        $auth = false;
        if ($is_admin || $token === $post_token) {
          $auth = true;
        }

        if (!$auth) {
          wp_send_json_error();
        }

        if (
          $recurring &&
          (!$recurring['status'] || !$recurring['subscription'])
        ) {
          unset($invoice['recurring']);
          $invoice['recurring']['status'] = false;
          $invoice['recurring']['subscription'] = false;
        }

        $payment_methods = isset($invoice['payment_methods'])
          ? $invoice['payment_methods']
          : null;
        if ($payment_methods) {
          $new_payment_methods = [];

          foreach ($payment_methods as $key => $payment_id) {
            $payment_query_data = [];
            $payment_query_data['id'] = $payment_id;

            if ($key === 'bank') {
              $payment_query_data['name'] = get_post_meta(
                $payment_id,
                'name',
                true
              );
              $payment_query_data['details'] = get_post_meta(
                $payment_id,
                'details',
                true
              );
            } elseif ($key === 'paypal') {
              $payment_query_data['account_type'] = get_post_meta(
                $payment_id,
                'account_type',
                true
              );
              $payment_query_data['client_id'] = get_post_meta(
                $payment_id,
                'client_id',
                true
              );
            } elseif ($key === 'stripe') {
              $payment_query_data['public_key'] = get_post_meta(
                $payment_id,
                'public_key',
                true
              );
            }

            $new_payment_methods[$key] = $payment_query_data;
          }

          $invoice['payment_methods'] = $new_payment_methods;
        }

        $invoice_model = new ModelInvoice();
        $invoice['total'] = $invoice_model->getTotalAmount($invoice);
      }
    } else {
      //new
      $get_taxonomy = Fns::get_terms('estinv_qty_type');
      $query_data['qty_type'] = '';
      if (isset($get_taxonomy[0])) {
        $query_data['qty_type'] = Fns::slugify($get_taxonomy[0]->name);
      }

      //set default template
      $tab = $param['path'] . '_template';
      $default_template = 5;
      $option = get_option('ndpv_' . $tab);
      if ($option) {
        $default_template = $option['default_template'];
      }
      $query_data['default_template'] = $default_template;
    }

    $path = $id ? get_post_meta($id, 'path', true) : $param['path'];
    $prefix = get_option('ndpv_' . $path . '_general');
    if ($prefix) {
      $query_data['prefix'] = $prefix['prefix'];
    } else {
      $query_data['prefix'] = ($path === 'invoice') ? 'Inv-' : 'Est-';
    }

    $query_data['wc'] = false;
    if (ndpv()->wage()) {
      $wc = get_option('ndpv_payment_wc');
      if (isset($wc['status'])) {
        if ($wc['status'] && class_exists('woocommerce')) {
          $query_data['wc'] = true;
        }
      }
    }

    $query_data['invoice'] = $invoice;

    wp_send_json_success($query_data);
  }

  public function create($req)
  {
    $param = $req->get_params();
    $reg_errors = new \WP_Error();
    //TODO: sanitize later
    $invoice = isset($param) ? $param : null;
    $num = isset($param['num']) ? $param['num'] : '';
    $module_id = isset($param['module_id']) ? $param['module_id'] : null;
    $date = isset($param['date']) ? $param['date'] : null;
    $path = isset($param['path']) ? $param['path'] : '';
    $due_date = isset($param['due_date']) ? $param['due_date'] : null;
    $payment_methods = isset($param['payment_methods'])
      ? $param['payment_methods']
      : null;

    $total = 0;
    foreach ($param['items'] as $item) {
      $total += $item['qty'] * $item['price'];
    }

    $from = isset($param['from']) ? $param['from'] : null;
    $to = isset($param['to']) ? $param['to'] : null;
    $to_type = isset($param['to_type']) ? $param['to_type'] : null;

    $reminder = isset($param['reminder']) ? $param['reminder'] : null;
    $recurring = isset($param['recurring']) ? $param['recurring'] : null;

    if (!$from) {
      $reg_errors->add(
        'field',
        esc_html__('Business is missing', 'propovoice')
      );
    }

    if (!$path) {
      $reg_errors->add(
        'field',
        esc_html__('Module is missing', 'propovoice')
      );
    }

    if (!$to) {
      $reg_errors->add(
        'field',
        esc_html__('Receiver is missing', 'propovoice')
      );
    }

    if ($reg_errors->get_error_messages()) {
      wp_send_json_error($reg_errors->get_error_messages());
    } else {
      //TODO: give proper title
      $title = '';
      $data = [
        'post_type' => 'ndpv_estinv',
        'post_title' => $title,
        'post_content' => '',
        'post_status' => 'publish',
        'post_author' => get_current_user_id(),
      ];
      $post_id = wp_insert_post($data);

      if (!is_wp_error($post_id)) {
        update_post_meta($post_id, 'ws_id', ndpv()->get_workspace());
        update_post_meta($post_id, 'status', 'draft');
        update_post_meta($post_id, 'path', $path);

        $auto_id = '';
        if ($num) {
          update_post_meta($post_id, 'num', $num);
        } else {
          //auto number
          $prefix = get_option('ndpv_' . $path . '_general');
          if ($prefix) {
            $prefix = $prefix['prefix'];
          } else {
            $prefix = ($path === 'invoice') ? 'Inv-' : 'Est-';
          }
          $auto_id = $prefix . Fns::auto_id($path);
          update_post_meta($post_id, 'num', $auto_id);
          $invoice['num'] = $auto_id;
        }

        if ($module_id) {
          update_post_meta($post_id, 'module_id', $module_id);
        }

        if ($date) {
          update_post_meta($post_id, 'date', $date);
        }

        if ($due_date) {
          update_post_meta($post_id, 'due_date', $due_date);
        }

        if ($from) {
          update_post_meta($post_id, 'from', $from);
        }

        if ($to) {
          update_post_meta($post_id, 'to', $to);
        }

        if ($to_type) {
          update_post_meta($post_id, 'to_type', $to_type);
        }

        if ($invoice) {
          update_post_meta($post_id, 'invoice', $invoice);
        }

        if ($total) {
          update_post_meta($post_id, 'total', $total);
        }

        if ($payment_methods) {
          update_post_meta(
            $post_id,
            'payment_methods',
            $payment_methods
          );
        }

        if ($reminder) {
          //save true or false
          update_post_meta($post_id, 'reminder', $reminder['status']);
        }

        if ($recurring) {
          //save true or false
          update_post_meta(
            $post_id,
            'recurring',
            $recurring['status']
          );
          update_post_meta(
            $post_id,
            'subscription',
            $recurring['subscription']
          );
        }

        //generate secret token
        $bytes = random_bytes(20);
        $token = bin2hex($bytes);
        update_post_meta($post_id, 'token', $token);
        $param['id'] = $post_id;
        $hook = ($path === 'invoice') ? 'inv' : 'est';

        do_action('ndpvp_webhook', $hook . '_add', $param);

        wp_send_json_success(
          [
            'id' => $post_id,
            'token' => $token,
            'auto_id' => $auto_id,
          ]
        );
      } else {
        wp_send_json_error();
      }
    }
  }

  public function update($req)
  {
    $param = $req->get_params();
    $reg_errors = new \WP_Error();
    $invoice = isset($param) ? $param : null;

    $num = isset($param['num']) ? $param['num'] : '';
    $module_id = isset($param['module_id']) ? $param['module_id'] : null;
    $path = isset($param['path']) ? $param['path'] : '';
    $date = isset($param['date']) ? $param['date'] : null;
    $due_date = isset($param['due_date']) ? $param['due_date'] : null;
    $payment_methods = isset($param['payment_methods'])
      ? $param['payment_methods']
      : null;

    $from = isset($param['from']) ? $param['from'] : null;
    $to = isset($param['to']) ? $param['to'] : null;
    $to_type = isset($param['to_type']) ? $param['to_type'] : null;

    $total = 0;
    foreach ($param['items'] as $item) {
      $total += $item['qty'] * $item['price'];
    }

    $reminder = isset($param['reminder']) ? $param['reminder'] : null;
    $recurring = isset($param['recurring']) ? $param['recurring'] : null;

    $attach = isset($param['attach']) ? $param['attach'] : null;
    $sign = isset($param['sign']) ? $param['sign'] : null;

    if (!$from) {
      $reg_errors->add(
        'field',
        esc_html__('Business is missing', 'propovoice')
      );
    }

    if (!$to) {
      $reg_errors->add(
        'field',
        esc_html__('Receiver is missing', 'propovoice')
      );
    }

    if ($reg_errors->get_error_messages()) {
      wp_send_json_error($reg_errors->get_error_messages());
    } else {
      $url_params = $req->get_url_params();
      $post_id = $url_params['id'];

      $data = [
        'ID' => $post_id,
        'post_title' => '',
        'post_content' => '',
      ];
      $post_id = wp_update_post($data);

      if (!is_wp_error($post_id)) {
        if ($module_id) {
          update_post_meta($post_id, 'module_id', $module_id);
        }

        update_post_meta($post_id, 'num', $num);
        update_post_meta($post_id, 'date', $date);

        update_post_meta($post_id, 'due_date', $due_date);

        if ($from) {
          update_post_meta($post_id, 'from', $from);
        }

        if ($to) {
          update_post_meta($post_id, 'to', $to);
        }

        if ($to_type) {
          update_post_meta($post_id, 'to_type', $to_type);
        }

        if ($invoice) {
          update_post_meta($post_id, 'invoice', $invoice);
        }

        if ($total) {
          update_post_meta($post_id, 'total', $total);
        }

        if ($reminder) {
          update_post_meta($post_id, 'reminder', $reminder['status']);
        }

        if ($recurring) {
          update_post_meta(
            $post_id,
            'recurring',
            $recurring['status']
          );
          update_post_meta(
            $post_id,
            'subscription',
            $recurring['subscription']
          );
        }

        update_post_meta($post_id, 'payment_methods', $payment_methods);

        $hook = ($path === 'invoice') ? 'inv' : 'est';
        do_action('ndpvp_webhook', $hook . '_edit', $param);

        wp_send_json_success($post_id);
      } else {
        wp_send_json_error();
      }
    }
  }

  public function delete($req)
  {
    $url_params = $req->get_url_params();

    $ids = explode(',', $url_params['id']);
    foreach ($ids as $id) {
      wp_delete_post($id);
    }

    do_action('ndpvp_webhook', 'inv_del', $ids);

    wp_send_json_success($ids);
  }

  // check permission
  public function get_per()
  {
    return current_user_can('ndpv_invoice') ||
      current_user_can('ndpv_estimate');
  }

  public function get_per_single()
  {
    return true;
  }

  public function create_per()
  {
    return current_user_can('ndpv_invoice') ||
      current_user_can('ndpv_estimate');
  }

  public function update_per()
  {
    return current_user_can('ndpv_invoice') ||
      current_user_can('ndpv_estimate');
  }

  public function del_per()
  {
    return current_user_can('ndpv_invoice') ||
      current_user_can('ndpv_estimate');
  }

  public function is_client($id)
  {
    return get_post_meta($id, 'is_client', true);
  }
}
