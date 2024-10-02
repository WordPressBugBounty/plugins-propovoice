<?php

namespace Ndpv\Api\Type;

use Ndpv\Helpers\Fns;
use Ndpv\Traits\Singleton;

class Media
{

  use Singleton;

  public function routes()
  {
    register_rest_route(
      'ndpv/v1',
      '/media/(?P<id>\d+)',
      [
        'methods' => 'GET',
        'callback' => [$this, 'get_single'],
        'permission_callback' => [$this, 'get_per'],
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
      '/media' . ndpv()->plain_route(),
      [
        'methods' => 'GET',
        'callback' => [$this, 'get'],
        'permission_callback' => [$this, 'get_per'],
      ]
    );

    register_rest_route(
      'ndpv/v1',
      '/media/attachment/(?P<type>\w+)' . ndpv()->plain_route(),
      [
        'methods' => 'GET',
        'callback' => [$this, 'get_attachment'],
        'permission_callback' => [$this, 'get_attachment_per'],
      ]
    );

    register_rest_route(
      'ndpv/v1',
      '/media/attachment/(?P<type>\w+)/default/get' . ndpv()->plain_route(),
      [
        'methods' => 'GET',
        'callback' => [$this, 'get_default_attachment'],
        'permission_callback' => [$this, 'get_attachment_per'],
      ]
    );

    register_rest_route(
      'ndpv/v1',
      '/media/attachment/(?P<type>\w+)/default/set/(?P<id>\d+)' . ndpv()->plain_route(),
      [
        'methods' => 'GET',
        'callback' => [$this, 'set_default_attachment'],
        'permission_callback' => [$this, 'get_attachment_per'],
      ]
    );
    register_rest_route(
      'ndpv/v1',
      '/media',
      [
        'methods' => 'POST',
        'callback' => [$this, 'create'],
        'permission_callback' => [$this, 'create_per'],
      ]
    );

    register_rest_route(
      'ndpv/v1',
      '/media/(?P<id>[0-9,]+)',
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
    $request = $req->get_params();

    $per_page = 10;
    $offset = 0;

    if (isset($request['per_page'])) {
      $per_page = $request['per_page'];
    }

    if (isset($request['page']) && $request['page'] > 1) {
      $offset = $per_page * $request['page'] - $per_page;
    }

    $args = [
      'post_type' => 'ndpv_estinv',
      'post_status' => 'publish',
      'posts_per_page' => $per_page,
      'offset' => $offset,
    ];

    $args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
      'relation' => 'OR',
    ];

    $query = new \WP_Query($args);
    $total_data = $query->found_posts; //use this for pagination
    $result = $data = [];
    while ($query->have_posts()) {
      $query->the_post();
      $id = get_the_ID();

      $query_data = [];
      $query_data['id'] = $id;

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

      $to_id = get_post_meta($id, 'to', true);
      $to_data = [];
      if ($to_id) {
        $to_data['id'] = $to_id;
        $to_obj = get_user_by('id', $to_id);

        $to_data['first_name'] = $to_obj->first_name;
        $to_data['last_name'] = $to_obj->last_name;
        $to_data['email'] = $to_obj->user_email;
      }
      $query_data['to'] = $to_data;

      $query_data['invoice'] = json_decode(
        get_post_meta($id, 'invoice', true)
      );

      $query_data['total'] = get_post_meta($id, 'total', true);
      $query_data['paid'] = get_post_meta($id, 'paid', true);
      if (!$query_data['paid']) {
        $query_data['paid'] = 0;
      }
      $query_data['due'] = get_post_meta($id, 'due', true);
      if (!$query_data['due']) {
        $query_data['due'] = 0;
      }

      $query_data['date'] = get_the_time(get_option('date_format'));
      $data[] = $query_data;
    }
    wp_reset_postdata();

    $result['result'] = $data;
    $result['total'] = $total_data;

    wp_send_json_success($result);
  }

  public function get_attachment($req)
  {
    $url_params = $req->get_url_params();
    $attach_type = $url_params['type'];
    $args = [
      'post_type' => 'attachment',
      'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
        [
          'key' => 'ndpv_attach_type',
          'value' => $attach_type,
          'meta_compare' => '=',
        ],
      ],
      'posts_per_page' => -1,
    ];
    $posts = get_posts($args);
    wp_send_json($posts);
    wp_reset_postdata();
  }

  public function get_default_attachment($req)
  {
    $url_params = $req->get_url_params();
    $attach_type = $url_params['type'];
    $args = [
      'post_type' => 'attachment',
      'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
        'relation' => 'AND',
        [
          'key' => 'ndpv_attach_type',
          'value' => $attach_type,
          'meta_compare' => '=',
        ],
        [
          'key' => 'ndpv_is_default_' . $attach_type,
          'value' => true,
          'meta_compare' => '=',
        ],

      ],
      'posts_per_page' => 1,
    ];
    $posts = get_posts($args);
    if (isset($posts[0])) {
      wp_send_json($posts[0]);
    }
    wp_reset_postdata();
  }

  public function set_default_attachment($req)
  {
    $url_params = $req->get_url_params();
    $attach_type = $url_params['type'];
    $new_post_id = $url_params['id'];

    $args = [
      'post_type' => 'attachment',
      'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
        'relation' => 'AND',
        [
          'key' => 'ndpv_attach_type',
          'value' => $attach_type,
          'meta_compare' => '=',
        ],
        [
          'key' => 'ndpv_is_default_' . $attach_type,
          'value' => true,
          'meta_compare' => '=',
        ],

      ],
      'posts_per_page' => -1,
    ];
    $posts = get_posts($args);
    foreach ($posts as $post) {
      delete_post_meta($post->ID, 'ndpv_is_default_' . $attach_type);
    }
    update_post_meta($new_post_id, 'ndpv_is_default_' . $attach_type, true);
    wp_send_json($new_post_id);
    wp_reset_postdata();
  }


  public function get_single($req)
  {
    $url_params = $req->get_url_params();
    $id = $url_params['id'];

    $query_data = [];
    $query_data['id'] = $id;

    $query_data['invoice'] = json_decode(
      get_post_meta($id, 'invoice', true)
    );

    $from_id = get_post_meta($id, 'from', true);
    $from_data = [];
    if ($from_id) {
      $from_data['id'] = $from_id;

      $from_meta = get_post_meta($from_id);

      $from_data['name'] = isset($from_meta['name'])
        ? $from_meta['name'][0]
        : '';
      $from_data['email'] = isset($from_meta['email'])
        ? $from_meta['email'][0]
        : '';
      $from_data['web'] = isset($from_meta['web'])
        ? $from_meta['web'][0]
        : '';
      $from_data['address'] = isset($from_meta['address'])
        ? $from_meta['address'][0]
        : '';
    }
    $query_data['fromData'] = $from_data;

    $to_id = get_post_meta($id, 'to', true);
    $to_data = [];
    if ($to_id) {
      $to_data['id'] = $to_id;
      $to_obj = get_user_by('id', $to_id);

      $to_data['first_name'] = $to_obj->first_name;
      $to_data['last_name'] = $to_obj->last_name;
      $to_data['email'] = $to_obj->user_email;
      $to_data['web'] = get_user_meta($to_id, 'web', true);
      $to_data['address'] = get_user_meta($to_id, 'address', true);
    }
    $query_data['toData'] = $to_data;

    wp_send_json_success($query_data);
  }

  public function create($req)
  {
    $file_params = $req->get_file_params();
    $param = $req->get_params();
    $file_data = isset($file_params['file']) ? $file_params['file'] : '';
    $attach_type = isset($param['attach_type'])
      ? $param['attach_type']
      : '';

    $reg_errors = new \WP_Error();

    $img_max_size = 5048; //5048KB

    $file = $file_data;
    $allowed_file_types = ['image/jpg', 'image/jpeg', 'image/png', 'application/pdf'];
    // Allowed file size -> 5MB
    $allowed_file_size = $img_max_size * 5048;

    if (!empty($file['name'])) {
      // Check file type
      if (!in_array($file['type'], $allowed_file_types)) {
        $valid_file_type = str_replace(
          'image/',
          '',
          implode(', ', $allowed_file_types)
        );
        $error_file_type = str_replace('image/', '', $file['type']);

        $reg_errors->add(
          'field',
          sprintf(
            // Translators: %1$s is the invalid file type provided by the user, %2$s is a list of supported file types
            esc_html__(
              'Invalid file type: %1$s. Supported file types: %2$s',
              'propovoice'
            ),
            $error_file_type,
            $valid_file_type
          )
        );
      }

      // Check file size
      /* if ($file["size"] > $allowed_file_size) {
                $reg_errors->add(
                    "field",
                    sprintf(
                        esc_html__(
                            "File is too large. Max. upload file size is %s",
                            "propovoice"
                        ),
                        Fns::format_bytes($allowed_file_size)
                    )
                );
            } */

      if ($reg_errors->get_error_messages()) {
        wp_send_json_error($reg_errors->get_error_messages());
      } else {
        if (!function_exists('wp_handle_upload')) {
          require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        $upload_overrides = ['test_form' => false];
        $uploaded = wp_handle_upload($file, $upload_overrides);

        if ($uploaded && !isset($uploaded['error'])) {
          $filename = $uploaded['file'];
          $filetype = wp_check_filetype(basename($filename), null);

          $attach_id = wp_insert_attachment(
            [
              'guid' => $uploaded['url'],
              'post_title' => sanitize_text_field(
                preg_replace(
                  '/\.[^.]+$/',
                  '',
                  basename($filename)
                )
              ),
              'post_excerpt' => '',
              'post_content' => '',
              'post_mime_type' => sanitize_text_field(
                $filetype['type']
              ),
              'comments_status' => 'closed',
            ],
            $uploaded['file'],
            0
          );

          $file_info = [];
          if (!is_wp_error($attach_id)) {
            // wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $filename));
            update_post_meta(
              $attach_id,
              'ws_id',
              ndpv()->get_workspace()
            );
            update_post_meta(
              $attach_id,
              'ndpv_attach_type',
              $attach_type
            );

            $file_info = [
              'id' => $attach_id,
              'type' => get_post_mime_type($attach_id),
              'name' => basename(get_attached_file($attach_id)),
              'src' => wp_get_attachment_image_url(
                $attach_id,
                'thumbnail'
              ),
            ];

            if ($file_info['type'] == 'application/pdf') {
              $file_info['name'] = basename(get_attached_file($attach_id));
              $file_info['src'] = wp_get_attachment_url($attach_id);
            }
          }

          wp_send_json_success($file_info);
        } else {
          /*
                     * Error generated by _wp_handle_upload()
                     * @see _wp_handle_upload() in wp-admin/includes/file.php
                     */
          wp_send_json_error([$uploaded['error']]);
        }
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
    wp_send_json_success($ids);
  }

  // check permission
  public function get_per()
  {
    return current_user_can('ndpv_media');
  }
  public function get_attachment_per()
  {
    return true;
  }

  public function create_per($req)
  {
    $param = $req->get_params();
    return isset($param['permission'])
      ? true
      : current_user_can('ndpv_media');
  }

  public function del_per()
  {
    return current_user_can('ndpv_media') || current_user_can('manage_options');
  }
}
