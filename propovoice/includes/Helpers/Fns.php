<?php

namespace Ndpv\Helpers;

use Ndpv\Models\Business;
use Ndpvp\Models\CustomField;

/**
 * Helper functions
 *
 * @since 1.0.0
 */
class Fns
{

  /**
   * Get custom fields depending on module name
   *
   * @since 1.0.0
   *
   * @param $mod
   *
   * @return array
   */
  public static function custom_field($mod = '')
  {
    if (ndpv()->wage()) {
      $custom_field = new CustomField();
      return $custom_field->get($mod);
    } else {
      return [];
    }
  }

  /**
   * Auto serial number for invoice and estimate
   *
   * @since 1.7.0
   *
   * @param $mod
   *
   * @return int
   */
  public static function auto_id($mod = '')
  {
    $total = get_option('ndpv_' . $mod . '_total');
    if (!$total) {
      //count max post id, other wise 1
      $new = 1;
      $args = [
        'post_type'      => 'ndpv_estinv',
        'posts_per_page' => 1,
        'meta_key'   => 'path', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
        'meta_value' => $mod, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
        'post_status' => 'publish',
        'orderby'     => 'date',
        'order'       => 'DESC',
      ];

      $latest_posts = get_posts($args);

      if ($latest_posts) {
        $last_post_id = $latest_posts[0]->ID;
        $new = $last_post_id + 1;
      }

      update_option('ndpv_' . $mod . '_total', $new);
      return $new;
    } else {
      $new_total = $total + 1;
      update_option('ndpv_' . $mod . '_total', $new_total);
      return $new_total;
    }
  }

  /**
   * Get taxonomy list depending on taxonomy name
   *
   * @since 1.0.0
   *
   * @param $tax
   *
   * @return array
   */
  public static function get_terms($tax)
  {
    $args = [
      'taxonomy' => 'ndpv_' . $tax,
      'meta_key' => 'tax_pos', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
      'orderby'  => 'meta_value_num',
      'order'    => 'ASC',
      'hide_empty' => false,
    ];

    return get_terms($args);
  }

  /**
   * Get taxonomy list depending on taxonomy and type
   *
   * @since 1.0.0
   *
   * @param $tax
   * @param $type
   *
   * @return int|null
   */
  public static function get_term_id_by_type($tax, $type)
  {
    $term_args = [
      'hide_empty' => false, // also retrieve terms which are not used yet
      'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
        [
          'key'     => 'type',
          'value'   => $type,
          'compare' => 'LIKE',
        ],
      ],
      'taxonomy' => 'ndpv_' . $tax,
    ];
    $terms = get_terms($term_args);
    if (isset($terms[0]->term_id)) {
      return $terms[0]->term_id;
    }
    return null;
  }

  /**
   * Get posts ids by type
   *
   * @since 1.0.0
   *
   * @param $post_type
   *
   * @return array
   */
  public static function get_posts_ids_by_type($post_type)
  {
    $user_id = get_current_user_id();
    $args1 = [
      'posts_per_page' => -1,
      'author' => $user_id,
      'fields' => 'ids',
      'post_type' => $post_type,
    ];
    $query1 = new \WP_Query($args1);

    $args2 = [
      'posts_per_page' => -1,
      'author__not_in' => [$user_id],
      'fields' => 'ids',
      'post_type' => $post_type,
    ];

    $args2['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
      'relation' => 'AND',
    ];
    $args2['meta_query'][] = [
      [
        'key' => '_ndpv_allowed_users',
        'value' => $user_id,
        'compare' => 'LIKE',
      ],
    ];
    $query2 = new \WP_Query($args2);
    $merge_ids_posts = array_merge($query1->posts, $query2->posts);
    wp_reset_postdata();
    return $merge_ids_posts;
  }

  /**
   * Project request limit helper for Service Package
   *
   * @since 1.0.0
   *
   * @param $order_id
   * @param $month
   *
   * @return int
   */
  public static function project_request_limit($order_id, $month = false)
  {
    $args = [
      'posts_per_page' => -1,
      'fields' => 'ids',
      'post_type' => 'ndpv_request',
      'post_status' => 'publish',
    ];

    $args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
      'relation' => 'AND',
    ];
    $args['meta_query'][] = [
      [
        'key' => 'tab_id',
        'value' => $order_id,
        'compare' => 'LIKE',
      ],
    ];

    if ($month) {
      $current_date = current_time('mysql');
      $first_day_of_month = gmdate('Y-m-01 00:00:00');
      $args['date_query'] = [
        [
          'after'     => $first_day_of_month,
          'before'    => $current_date,
          'inclusive' => true,
        ],
      ];
    }

    if (!$month) {
      $term_id = self::get_term_id_by_type('request_status', 'delivered');
      $args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
        [
          'taxonomy' => 'ndpv_request_status',
          'terms' => [$term_id],
          'field' => 'id',
          'operator' => 'NOT IN',
        ],
      ];
    }
    $query = new \WP_Query($args);
    $found_posts = $query->found_posts;
    wp_reset_postdata();
    return $found_posts;
  }

  /**
   * Check contact exist or not depending on key
   *
   * @since 1.0.0
   *
   * @param $post_type
   * @param $value
   * @param $key
   *
   * @return int|null
   */
  public static function contact_exist($post_type, $value = '', $key = 'email')
  {
    if ($value) {
      $args = [
        'post_type' => 'ndpv_' . $post_type,
        'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
          [
            'key' => $key,
            'value' => $value,
          ],
        ],
        'fields' => 'ids',
      ];
      $number_query = new \WP_Query($args);
      $ids = $number_query->posts;
      if (!empty($ids)) {
        return $ids[0];
      }
    }
    return null;
  }

  /**
   * Get brand logo from setting
   *
   * @since 1.0.0
   *
   * @return array
   */
  public static function brand_logo()
  {
    $white_label = get_option('ndpv_white_label');
    $logo = null;
    if ($white_label && isset($white_label['logo'])) {
      $data['logo'] = $white_label['logo'];
      $logo_id = $white_label['logo'];
      $logo_data = null;
      if ($logo_id) {
        $logo_src = wp_get_attachment_image_src($logo_id, 'thumbnail');
        if ($logo_src) {
          $logo_data = [];
          $logo_data['id'] = $logo_id;
          $logo_data['src'] = $logo_src[0];
        }
      }
      $logo = $logo_data;
    }
    return $logo;
  }

  /**
   * Ready date format for PHP to JS
   *
   * @since 1.0.0
   *
   * @param $format
   *
   * @return string
   */
  public static function phpToMomentFormat($format)
  {
    $replacements = [
      'd' => 'DD',
      'D' => 'ddd',
      'j' => 'D',
      'l' => 'dddd',
      'N' => 'E',
      'S' => 'o',
      'w' => 'e',
      'z' => 'DDD',
      'W' => 'W',
      'F' => 'MMMM',
      'm' => 'MM',
      'M' => 'MMM',
      'n' => 'M',
      't' => '', // no equivalent
      'L' => '', // no equivalent
      'o' => 'YYYY',
      'Y' => 'YYYY',
      'y' => 'YY',
      'a' => 'a',
      'A' => 'A',
      'B' => '', // no equivalent
      'g' => 'h',
      'G' => 'H',
      'h' => 'hh',
      'H' => 'HH',
      'i' => 'mm',
      's' => 'ss',
      'u' => 'SSS',
      'e' => 'zz', // deprecated since version 1.6.0 of moment.js
      'I' => '', // no equivalent
      'O' => '', // no equivalent
      'P' => '', // no equivalent
      'T' => '', // no equivalent
      'Z' => '', // no equivalent
      'c' => '', // no equivalent
      'r' => '', // no equivalent
      'U' => 'X',
    ];
    $moment_format = strtr($format, $replacements);
    return $moment_format;
  }

  /**
   * Get template location
   *
   * @since 1.0.0
   *
   * @param $name
   *
   * @return string
   */
  public static function locate_template($name)
  {
    // Look within passed path within the theme - this is priority.
    $template = [];

    $template[] = ndpv()->get_template_path() . $name . '.php';

    if (!$template_file = locate_template(apply_filters('ndpv_locate_template_names', $template))) {
      $template_file = NDPV_PATH . "templates/$name.php";
    }

    return apply_filters('ndpv_locate_template', $template_file, $name);
  }

  /**
   * Get client page url by template slug
   *
   * @since 1.0.0
   *
   * @param $slug
   *
   * @return string
   */
  public static function client_page_url($slug)
  {
    $page = get_pages(
      [
        'meta_key' => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
        'meta_value' => $slug . '-template.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
      ]
    );
    if (!empty($page)) {
      return get_permalink($page[0]->ID);
    } else {
      return '';
    }
  }

  /**
   * Support variable in settings for email template
   *
   * @since 1.0.0
   *
   * @param $content
   * @param $array
   *
   * @return void
   */
  public static function templateVariable($content = '', $array = [])
  {
    $id = isset($array['id']) ? $array['id'] : '';
    $path = isset($array['path']) ? $array['path'] : '';
    $title = isset($array['title']) ? $array['title'] : '';
    $org_name = isset($array['org_name']) ? $array['org_name'] : '';
    $org_img = isset($array['org_img']) ? $array['org_img'] : '';
    $org_address = isset($array['org_address']) ? $array['org_address'] : '';
    $client_name = isset($array['client_name']) ? $array['client_name'] : '';
    $date = isset($array['date']) ? $array['date'] : '';
    $due_date = isset($array['due_date']) ? $array['due_date'] : '';
    $amount = isset($array['amount']) ? $array['amount'] : '';
    $msg = isset($array['msg']) ? $array['msg'] : '';
    $url = isset($array['url']) ? $array['url'] : '';

    $footer_text = '<p>Powered by</p>
<h3>Propovoice</h3>';
    $email_footer = get_option('ndpv_email_footer');
    if (ndpv()->wage()) {
      if (isset($email_footer['text'])) {
        $footer_text = $email_footer['text'];
      }
    }

    $social = '';
    //TODO: set free version social link
    $social_list = [
      [
        'label' => '',
        'url' => 'https://wordpress.org/plugins/propovoice',
        'icon' => ndpv()->get_asset_uri('img/email/') . 'wp.png',
      ],
      [
        'label' => '',
        'url' => 'https://www.facebook.com/propovoice',
        'icon' => ndpv()->get_asset_uri('img/email/') . 'facebook.png',
      ],
      [
        'label' => '',
        'url' => 'https://twitter.com/nasirbinburhan',
        'icon' => ndpv()->get_asset_uri('img/email/') . 'twitter.png',
      ],
    ];

    if (ndpv()->wage()) {
      $get_taxonomy = self::get_terms('email_social');
      $format_taxonomy = [];
      foreach ($get_taxonomy as $single) {
        $icon_id = get_term_meta($single->term_id, 'icon', true);
        $label = $single->name;
        $term_url = get_term_meta($single->term_id, 'url', true);
        $icon_src = '';
        if ($icon_id) {
          $icon_src = wp_get_attachment_image_src($icon_id, 'thumbnail');
          if ($icon_src) {
            $icon_src = $icon_src[0];
          }
        }

        $format_taxonomy[] = [
          'label' => $label,
          'url' => $term_url,
          'icon' => $icon_src,
        ];
      }
      if ($format_taxonomy) {
        $social_list = $format_taxonomy;
      }
    }

    foreach ($social_list as $key => $val) {
      $margin_right = $key === array_key_last($social_list) ? 0 : '15px';
      if ($val['url']) {
        $social .= '<li style="display: inline-block;text-align:center;vertical-align:middle;margin-right:' . $margin_right . ';"><a href="' . esc_url($val['url']) . '" style="display: table-cell;list-style: none;padding: 7px;width: 23px;height: 23px;background-color: #fff;border-radius: 100%;line-height: 16px;text-align: center;vertical-align: middle;text-decoration: none;color: #2D3748; ' . ($val['icon'] ? '' : 'font-size: 15px; padding: 7px 15px; border-radius: 10px') . '">' . ($val['icon'] ? '<img src="' . esc_url($val['icon']) . '" alt="" style="max-height: 16px !important;max-width: 16px !important;">' : $val['label']) . '</a></li>';
      }
    }

    if (isset($email_footer['active']) && !$email_footer['active']) {
      $footer_text = '';
      $social = '';
    }

    return str_replace(
      [
        '{id}',
        '{path}',
        '{title}',
        '{org_name}',
        '{org_img}',
        '{org_address}',
        '{client_name}',
        '{date}',
        '{due_date}',
        '{amount}',
        '{msg}',
        '{url}',
        '{footer_text}',
        '{social}',
        '{view_txt}',
      ],
      [
        $id,
        $path,
        $title,
        $org_name,
        $org_img,
        $org_address,
        $client_name,
        $date,
        $due_date,
        $amount,
        $msg,
        $url,
        $footer_text,
        $social,
        esc_html__('View', 'propovoice'),
      ],
      $content
    );
  }

  /**
   * Get template part (for templates like the shop-loop).
   *
   * NDPV_TEMPLATE_DEBUG_MODE will prevent overrides in themes from taking priority.
   *
   * @param mixed  $slug Template slug.
   * @param string $name Template name (default: '').
   */
  public static function get_template_part($slug, $args = null, $is_include = true)
  {
    // load template from theme if exist
    $template = NDPV_TEMPLATE_DEBUG_MODE ? '' : locate_template(
      [
        "{$slug}.php",
        ndpv()->get_template_path() . "{$slug}.php",
      ]
    );

    // load template from pro plugin if exist
    if (!$template && function_exists('ndpvp')) {
      $fallback = ndpv()->plugin_path() . '-pro' . "/templates/{$slug}.php";
      $template = file_exists($fallback) ? $fallback : '';
    }

    // load template from current plugin if exist
    if (!$template) {
      $fallback = ndpv()->plugin_path() . "/templates/{$slug}.php";
      $template = file_exists($fallback) ? $fallback : '';
    }

    // Allow 3rd party plugins to filter template file from their plugin.
    $template = apply_filters('ndpv_get_template_part', $template, $slug);

    if ($template) {
      if (!empty($args) && is_array($args)) {
        extract($args); // @codingStandardsIgnoreLine
      }

      // load_template($template, false, $args);
      if ($is_include) {
        include $template;
      } else {
        return $template;
      }
    }
  }

  public static function doing_it_wrong($function, $message, $version)
  {
    // @codingStandardsIgnoreStart
    $message .= ' Backtrace: ' . wp_debug_backtrace_summary();
    _doing_it_wrong($function, $message, $version);
  }

  public static function get_template($fileName, $args = null)
  {
    if (!empty($args) && is_array($args)) {
      extract($args); // @codingStandardsIgnoreLine
    }

    $located = self::locate_template($fileName);

    if (!file_exists($located)) {
      /* translators: %s template */
      self::doing_it_wrong(__FUNCTION__, sprintf(__('%s does not exist.', 'propovoice'), '<code>' . $located . '</code>'), '1.0');

      return;
    }

    // Allow 3rd party plugin filter template file from their plugin.
    $located = apply_filters('ndpv_get_template', $located, $fileName, $args);

    do_action('ndpv_before_template_part', $fileName, $located, $args);

    include $located;

    do_action('ndpv_after_template_part', $fileName, $located, $args);
  }

  /**
   * Get option value by id
   *
   * @since 1.0.0
   *
   * @param $id
   *
   * @return bool|mixed|void
   */
  public static function get_option($id)
  {
    if (!$id) {
      return false;
    }
    $settings = get_option($id, []);

    return apply_filters($id, $settings);
  }

  /**
   * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
   * Non-scalar values are ignored.
   *
   * @param string|array $var Data to sanitize.
   *
   * @return string|array
   */
  public static function clean($var)
  {
    if (is_array($var)) {
      return array_map([self::class, 'clean'], $var);
    } else {
      return is_scalar($var) ? sanitize_text_field($var) : $var;
    }
  }

  /**
   * Get gravatar image by email
   *
   * @since 1.0.0
   *
   * @param $email
   * @param $size
   *
   * @return string
   */
  public static function gravatar($email, $size = 40)
  {
    $hash = md5(strtolower(trim($email)));
    return sprintf('https://www.gravatar.com/avatar/%s?s=%s', $hash, $size);
  }

  /**
   * Make slug from content
   *
   * @since 1.0.0
   *
   * @param $content
   *
   * @return string
   */
  public static function slugify($content)
  {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $content), '-'));
  }

  /**
   * Make slug key from content
   *
   * Here slug will make by _ not -
   *
   * @since 1.0.0
   *
   * @param $content
   *
   * @return string
   */
  public static function slugify_key($content)
  {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $content), '_'));
  }

  /**
   * Sanitize data when output
   *
   * @since 1.0.0
   *
   * @param $value
   * @param $type
   *
   * @return void
   */
  public function sanitizeOutPut($value, $type = 'text')
  {
    $newValue = null;
    if ($value) {
      if ($type == 'text') {
        $newValue = esc_html(stripslashes($value));
      } elseif ($type == 'url') {
        $newValue = esc_url(stripslashes($value));
      } elseif ($type == 'textarea') {
        $newValue = esc_textarea(stripslashes($value));
      } else {
        $newValue = esc_html(stripslashes($value));
      }
    }
    return $newValue;
  }

  /**
   * Get image information by id
   *
   * @since 1.0.0
   *
   * @param $attachment_id
   *
   * @return void
   */
  public function imageInfo($attachment_id)
  {
    $data = array();
    $img_data = wp_get_attachment_metadata($attachment_id);
    $data['id'] = $attachment_id;
    $data['url'] = wp_get_attachment_url($attachment_id);
    $data['width'] = !empty($img_data['width']) ? absint($img_data['width']) : 0;
    $data['height'] = !empty($img_data['height']) ? absint($img_data['height']) : 0;

    return $data;
  }

  /**
   * Format byte to label
   *
   * @since 1.0.0
   *
   * @param $bytes
   *
   * @return void
   */
  public static function format_bytes($bytes)
  {
    $label = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $bytes >= 1024 && $i < (count($label) - 1); $bytes /= 1024, $i++);
    return (round($bytes, 2) . " " . $label[$i]);
  }

  /**
   * Password mail
   *
   * @since 1.0.0
   *
   *
   * @return void
   */
  public static function password_mail($name, $email, $password, $type = 'client_portal')
  {
    //sent mail
    $data = [];

    $option = get_option('ndpv_email_' . $type . '_password');
    if ($option) {
      $data = $option;
    } else {
      $data['subject'] = ndpv()->get_default('email_template', $type, 'password', 'subject');
      $data['msg'] = ndpv()->get_default('email_template', $type, 'password', 'msg');
    }

    $mail_subject = $data['subject'];
    $msg = nl2br($data['msg']);

    $business = new Business;
    $business_info = $business->info();
    $org_name = $business_info['name'];
    $org_email = $business_info['email'];
    $permalink = Fns::client_page_url("workspace");
    $login_url =  "<a href='$permalink'>$permalink</a>";

    $subject = self::pass_email_variable($mail_subject, [
      "org_name" => $org_name,
    ]);
    $template = ndpv()->render("email/password", [], true);
    $template = str_replace('{msg}', $msg, $template);
    $body = self::pass_email_variable($template, [
      "org_name" => $org_name,
      "client_name" => $name,
      "login_url" => $login_url,
      "email" => $email,
      "password" => $password,
    ]);

    $headers = ["Content-Type: text/html; charset=UTF-8"];
    $headers[] = "From: " . $org_name . " <" . $org_email . ">";
    return wp_mail($email, $subject, $body, $headers, []);
  }

  /**
   * Format variable from string
   *
   * @since 1.0.0
   *
   * @param $content
   * @param $array
   *
   * @return void
   */
  private static function pass_email_variable($content, $array = [])
  {
    $org_name = isset($array['org_name']) ? $array['org_name'] : '';
    $client_name = isset($array['client_name']) ? $array['client_name'] : '';
    $login_url = isset($array['login_url']) ? $array['login_url'] : '';
    $email = isset($array['email']) ? $array['email'] : '';
    $password = isset($array['password']) ? $array['password'] : '';
    return str_replace(
      array(
        '{org_name}',
        '{client_name}',
        '{login_url}',
        '{email}',
        '{password}'
      ),
      array(
        $org_name,
        $client_name,
        $login_url,
        $email,
        $password
      ),
      $content
    );
  }
}
