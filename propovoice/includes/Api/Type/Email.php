<?php

namespace Ndpv\Api\Type;

use Ndpv\Helpers\Fns;
use Ndpv\Traits\Singleton;
use Ndpv\Models\Business;

class Email
{

  use Singleton;

  public function routes()
  {
    register_rest_route(
      'ndpv/v1',
      '/emails/(?P<id>\d+)',
      [
        'methods'             => 'GET',
        'callback'            => [$this, 'get_single'],
        'permission_callback' => [$this, 'get_per'],
        'args'                => [
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
      '/emails' . ndpv()->plain_route(),
      [
        'methods'             => 'GET',
        'callback'            => [$this, 'get'],
        'permission_callback' => [$this, 'get_per'],
      ]
    );

    register_rest_route(
      'ndpv/v1',
      '/emails',
      [
        'methods'             => 'POST',
        'callback'            => [$this, 'create'],
        'permission_callback' => [$this, 'create_per'],
      ]
    );

    register_rest_route(
      'ndpv/v1',
      '/emails/(?P<id>[0-9,]+)',
      [
        'methods'             => 'DELETE',
        'callback'            => [$this, 'delete'],
        'permission_callback' => [$this, 'del_per'],
        'args'                => [
          'id' => [
            'sanitize_callback' => 'sanitize_text_field',
          ],
        ],
      ]
    );

    // email sending features

    register_rest_route(
      'ndpv/v1',
      '/send-email',
      [
        'methods'             => 'POST',
        'callback'            => [$this, 'send_email'],
        'permission_callback' => [$this, 'create_per'],
      ]
    );

    register_rest_route(
      'ndpv/v1',
      '/email-logs',
      [
        'methods'             => 'POST',
        'callback'            => [$this, 'get_email_logs'],
        'permission_callback' => [$this, 'get_per'],
      ]
    );

    register_rest_route(
      'ndpv/v1',
      '/delete-email-logs',
      [
        'methods'             => 'POST',
        'callback'            => [$this, 'delete_email_logs'],
        'permission_callback' => [$this, 'get_per'],
      ]
    );

    // Custom email template
    register_rest_route(
      'ndpv/v1',
      '/save-custom-email',
      [
        'methods'             => 'POST',
        'callback'            => [$this, 'save_custom_email'],
        'permission_callback' => [$this, 'get_per'],
      ]
    );

    register_rest_route(
      'ndpv/v1',
      '/custom-email-templates',
      [
        'methods'             => 'POST',
        'callback'            => [$this, 'get_custom_email'],
        'permission_callback' => [$this, 'get_per'],
      ]
    );

    register_rest_route(
      'ndpv/v1',
      '/delete-custom-email-template',
      [
        'methods'             => 'POST',
        'callback'            => [$this, 'delete_custom_email_template'],
        'permission_callback' => [$this, 'get_per'],
      ]
    );
  }

  public function get_custom_email($request)
  {

    $post_meta = get_option('pv_custom_email_templates', true);

    // If the post meta is empty or doesn't exist, return an empty array
    if (empty($post_meta) || !is_array($post_meta)) {
      return [];
    }

    $result = [];
    foreach ($post_meta as $log) {
      array_push($result, $log);
    }

    return $result;
  }

  public function save_custom_email($request)
  {
    $param = $request->get_params();

    $new_item = [
      'id'      => time(),
      'name'    => $param['name'],
      'subject' => $param['subject'],
      'message' => $param['message'],
    ];
    // Get the existing array from post meta
    $existing_array = get_option('pv_custom_email_templates', true);

    // If the existing array is empty or doesn't exist, create a new array
    if (empty($existing_array) || !is_array($existing_array)) {
      $existing_array = [];
    }

    // Add the new item to the existing array
    $existing_array[] = $new_item;

    // Update the post meta with the modified array
    update_option('pv_custom_email_templates', $existing_array);

    wp_send_json_success();
  }

  public function delete_custom_email_template($request)
  {
    $param = $request->get_params();

    $id_to_remove = $param['id'];

    // Get the existing array from post meta
    $existing_array = get_option('pv_custom_email_templates', true);

    // If the existing array is empty or doesn't exist, nothing to remove
    if (empty($existing_array) || !is_array($existing_array)) {
      return;
    }

    // Iterate through the array and find the item to remove by ID
    foreach ($existing_array as $key => $item) {
      if (isset($item['id']) && $item['id'] === $id_to_remove) {
        // Remove the item from the array
        unset($existing_array[$key]);
        break; // Exit the loop once the item is found and removed
      }
    }

    // Update the post meta with the modified array
    update_option('pv_custom_email_templates', $existing_array);
    return wp_send_json_success();
  }
  public function delete_email_logs($request)
  {
    $param        = $request->get_params();
    $post_id      = $param['postId'];
    $id_to_remove = $param['id'];

    // Get the existing array from post meta
    $existing_array = get_post_meta($post_id, 'email_logs', true);

    // If the existing array is empty or doesn't exist, nothing to remove
    if (empty($existing_array) || !is_array($existing_array)) {
      return;
    }

    // Iterate through the array and find the item to remove by ID
    foreach ($existing_array as $key => $item) {
      if (isset($item['id']) && $item['id'] === $id_to_remove) {

        // Remove attachment media
        $attachments = $item['attachments'];
        if (isset($attachments['id'])) {
          wp_delete_attachment($attachments['id'], true);
        }

        // Remove the item from the array
        unset($existing_array[$key]);

        break; // Exit the loop once the item is found and removed
      }
    }

    // Update the post meta with the modified array
    update_post_meta($post_id, 'email_logs', $existing_array);
    return wp_send_json_success();
  }

  public function get_email_logs($request)
  {
    $param   = $request->get_params();
    $post_id = $param['postId'];

    $post_meta = get_post_meta($post_id, 'email_logs', true);

    // If the post meta is empty or doesn't exist, return an empty array
    if (empty($post_meta) || !is_array($post_meta)) {
      return [];
    }

    $result = [];
    foreach ($post_meta as $log) {
      array_push($result, $log);
    }
    return $result;
  }

  private function save_sent_message_to_db($post_id, $new_item)
  {
    // Get the existing array from post meta
    $existing_array = get_post_meta($post_id, 'email_logs', true);

    // If the existing array is empty or doesn't exist, create a new array
    if (empty($existing_array) || !is_array($existing_array)) {
      $existing_array = [];
    }

    // Add the new item to the existing array
    $existing_array[] = $new_item;

    // Update the post meta with the modified array
    update_post_meta($post_id, 'email_logs', $existing_array);
  }

  public function send_email($request)
  {
    $param = $request->get_params();

    $to      = $param['to']; // Primary recipient
    $subject = $param['subject'];
    $message = $param['message'];
    $post_id = $param['postId'];

    // Get the current site URL
    $site_url = get_site_url();
    // Get the content directory path
    $content_dir = WP_CONTENT_DIR;

    $attachments = $param['attachments'];
    // Modify attachment url to absolute path
    $abs_attachments = [];
    // Iterate through each attachment and modify the URLs
    foreach ($attachments as $key => $attachment_url) {
      $abs_attachments[$key] = str_replace($site_url . '/wp-content', $content_dir, $attachment_url);
    }

    // Define CC and BCC recipients
    $cc  = $param['cc'] ?? null;
    $bcc = $param['bcc'] ?? null;

    // Set headers for CC and BCC
    $headers = [
      'Content-Type: text/html; charset=UTF-8',
    ];

    // if cc is avilable for the mail,
    // then set it to header
    if ($cc) {
      array_push($headers, 'Cc: ' . $cc);
    }

    // if bcc is avilable for email,
    // then set it to header

    if ($bcc) {
      array_push($headers, 'Bcc: ' . $bcc);
    }
    // Send the email

    $business      = new Business();
    $business_info = $business->info();

    $compnay_name = $business_info['name'];
    $mail_from    = $business_info['email'];

    $headers[] = 'From: ' . $compnay_name . ' <' . $mail_from . '>';

    $result = wp_mail($to, $subject, $message, $headers, $abs_attachments);

    if ($result) {
      $data = [
        'id'          => time(),
        'subject'     => $subject,
        'content'     => $message,
        'attachments' => $attachments,
      ];
      $this->save_sent_message_to_db($post_id, $data);
      wp_send_json_success();
    } else {
      wp_send_json_error();
    }
  }

  public function get($req)
  {
    $request = $req->get_params();

    wp_send_json_success();
  }

  public function get_single($req)
  {
    $url_params = $req->get_url_params();
    $id         = $url_params['id'];
    wp_send_json_success();
  }

  public function create($req)
  {
    $param = $req->get_params();
    $type  = isset($param['type']) ? $param['type'] : '';
    if ($type === 'sent') {
      $this->sent($param);
    } elseif ($type === 'feedback') {
      $this->feedback($param);
    } elseif ($type === 'dashboard') {
      $this->dashboard($param);
    }
  }

  public function sent($param)
  {
    $org_id      = isset($param['fromData']) ? $param['fromData']['id'] : '';
    $org_name    = isset($param['fromData']) ? $param['fromData']['name'] : '';
    $org_img     = '';
    $org_address = '';
    if ($org_id) {
      $query_meta = get_post_meta($org_id);
      $logo_id   = isset($query_meta['logo']) ? $query_meta['logo'][0] : '';
      $address   = isset($query_meta['address'])
        ? $query_meta['address'][0]
        : '';
      $email     = isset($query_meta['email']) ? $query_meta['email'][0] : '';
      $mobile    = isset($query_meta['mobile'])
        ? $query_meta['mobile'][0]
        : '';

      if ($logo_id) {
        $logo_src = wp_get_attachment_image_src($logo_id, 'thumbnail');
        if ($logo_src) {
          $org_img = "<img src='" . $logo_src[0] . "' alt='' style='max-width: 200px !important;max-height: 90px !important;'/>";
        }
      }

      if ($address) {
        $org_address .= $address . '<br />';
      }
      $org_address .= $email;
      if ($mobile) {
        $org_address .= ',<br />' . $mobile;
      }
    }

    $mail_from        = isset($param['fromData'])
      ? $param['fromData']['email']
      : '';
    $mail_to          = isset($param['toData']) ? $param['toData']['email'] : '';
    $invoice_id       = isset($param['invoice_id']) ? $param['invoice_id'] : '';
    $path             = isset($param['path']) ? $param['path'] : '';
    $title            = isset($param['title']) ? $param['title'] : '';
    $mail_subject     = isset($param['subject']) ? $param['subject'] : '';
    $msg              = isset($param['msg']) ? nl2br($param['msg']) : '';
    $mail_invoice_img = isset($param['invoice_img'])
      ? $param['invoice_img']
      : '';

    $compnay_name = isset($param['fromData'])
      ? $param['fromData']['name']
      : '';

    $token = get_post_meta($invoice_id, 'token', true);
    $url   = sprintf(
      '%s?id=%s&token=%s',
      Fns::client_page_url($path),
      $invoice_id,
      $token
    );

    $subject  = Fns::templateVariable($mail_subject, []);
    $template = ndpv()->render('email/invoice', [], true);

    $body = Fns::templateVariable(
      $template,
      [
        'msg'         => $msg,
        'url'         => $url,
        'path'        => $path,
        'title'       => $title,
        'org_name'    => $org_name,
        'org_img'     => $org_img,
        'org_address' => $org_address,
      ]
    );

    $headers   = ['Content-Type: text/html; charset=UTF-8'];
    $headers[] = 'From: ' . $compnay_name . ' <' . $mail_from . '>';
    // TODO: dynamic Cc later
    // $headers[] = 'Cc: Rakib <therakib7@gmail.com>';
    // $headers[] = 'Cc: therakib7@gmail.com'; // note you can just use a simple email address

    // attachment
    $attachments = [];

    $send_mail = wp_mail($mail_to, $subject, $body, $headers, $attachments);

    if ($send_mail) {
      $status = get_post_meta($invoice_id, 'status', true);
      if ($status === 'draft') {
        update_post_meta($invoice_id, 'status', 'sent');
      }
      wp_send_json_success($send_mail);
    } else {
      wp_send_json_error(['Something wrong: Email not sent']);
    }
  }

  public function feedback($param)
  {
    $invoice_id    = isset($param['invoice_id']) ? $param['invoice_id'] : '';
    $given_token    = isset($param['token']) ? $param['token'] : '';
    $feedback_type = isset($param['feedback_type'])
      ? $param['feedback_type']
      : '';
    $note          = isset($param['note']) ? nl2br($param['note']) : '';
    $attachment    = isset($param['attachment']) ? $param['attachment'] : '';
    $saved_token = get_post_meta($invoice_id, 'token', true);

    if ($invoice_id && $given_token == $saved_token) {
      update_post_meta($invoice_id, 'status', $feedback_type);
      $feedback               = [];
      $feedback['type']       = $feedback_type;

      /* translators: This string contains a placeholder for the feedback message. */
      $feedback['note']       = esc_html($note);
      $feedback['attachment'] = $attachment;
      $feedback['time']       = current_time('timestamp');
      update_post_meta($invoice_id, 'feedback', $feedback);
    } else {
      wp_send_json_error();
    }

    $param['post_id'] = $invoice_id;
    $param['id']      = $invoice_id;
    if ($feedback_type === 'accept') {
      do_action('ndpvp_webhook', 'est_accept', $param);
    }
    if ($feedback_type === 'decline') {
      do_action('ndpvp_webhook', 'est_reject', $param);
    }

    wp_send_json_success();
  }

  public function dashboard($param)
  {
    $feedback_type  = isset($param['feedback_type'])
      ? $param['feedback_type']
      : '';
    $feedback_title = '';
    if ($feedback_type === 'features') {
      $feedback_title = 'Features Request: ';
    } else {
      $feedback_title = 'Bug Information: ';
    }

    $current_user = wp_get_current_user();
    $name         = isset($param['name'])
      ? $param['name']
      : $current_user->display_name;
    $from         = isset($param['from'])
      ? $param['from']
      : $current_user->user_email;
    $subject      = isset($param['subject']) ? $param['subject'] : '';
    $details      = isset($param['details']) ? nl2br($param['details']) : '';
    // TODO: change name email
    $propovoice_mail = 'support@propovoice.com';

    $headers   = ['Content-Type: text/html; charset=UTF-8'];
    $headers[] = 'From: ' . $name . ' <' . $from . '>';

    // attachment
    $attachments = [];
    $subject     = $feedback_title . $subject;
    $send_mail   = wp_mail(
      $propovoice_mail,
      $subject,
      $details,
      $headers,
      $attachments
    );

    if ($send_mail) {
      wp_send_json_success($send_mail);
    } else {
      wp_send_json_error(['Something wrong']);
    }
  }

  public function delete($req)
  {
    $url_params = $req->get_url_params();

    $ids = explode(',', $url_params['id']);
    foreach ($ids as $id) {
      wp_delete_post($id);
    }

    wp_send_json_success($ids);
  }


  // check permission
  public function get_per()
  {
    return current_user_can('ndpv_email');
  }

  public function create_per()
  {
    return true;
  }

  public function update_per()
  {
    return current_user_can('ndpv_email');
  }

  public function del_per()
  {
    return current_user_can('ndpv_email');
  }
}
