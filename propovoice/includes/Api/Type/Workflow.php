<?php

namespace Ndpv\Api\Type;

use Ndpv\Traits\Singleton;

class Workflow
{


  use Singleton;

  private $path = '/workflows';
  private $table_name = 'workflows';

  public function __construct()
  {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    global $wpdb;
    $table_name = esc_sql($wpdb->prefix . $this->table_name);

    // SQL to create workflows table
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        module tinytext NOT NULL,
        action tinytext NOT NULL,
        config text NOT NULL,
        action_data text NOT NULL,
        status tinytext NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    maybe_create_table($table_name, $sql);
  }

  public function routes()
  {
    // Get specific workflow.
    register_rest_route(
      'ndpv/v1',
      $this->path . '/(?P<id>\d+)',
      [
        'methods' => 'GET',
        'callback' => [$this, 'get_workflow'],
        'permission_callback' => [$this, 'get_permission'],
        'args' => [
          'id' => [
            'validate_callback' => function ($param) {
              return is_numeric($param);
            },
          ],
        ],
      ]
    );

    // get all workflows.
    register_rest_route(
      'ndpv/v1',
      $this->path,
      [
        'methods' => 'GET',
        'callback' => [$this, 'get_workflows'],
        'permission_callback' => [$this, 'get_permission'],
      ]
    );

    // create new workflow.
    register_rest_route(
      'ndpv/v1',
      $this->path,
      [
        'methods' => 'POST',
        'callback' => [$this, 'create_workflow'],
        'permission_callback' => [$this, 'create_permission'],
      ]
    );

    // delete workflow.
    register_rest_route(
      'ndpv/v1',
      $this->path . '/delete',
      [
        'methods' => 'POST',
        'callback' => [$this, 'delete_workflow'],
        'permission_callback' => [$this, 'create_permission'],
      ]
    );

    // update a workflow.

    register_rest_route(
      'ndpv/v1',
      $this->path,
      [
        'methods' => 'POST',
        'callback' => [$this, 'update_workflow'],
        'permission_callback' => [$this, 'create_permission'],
      ]
    );
  }

  public function delete_workflow($request)
  {

    global $wpdb;
    $id = $request->get_param('id');

    $table_name = esc_sql($wpdb->prefix . $this->table_name);

    // Prepare the SQL query
    if ($id) {
      // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
      $wpdb->delete($table_name, ['id' => $id], ['%d']);
    } else {
      foreach ($request->get_params() as $id) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->delete($table_name, ['id' => $id], ['%d']);
      }

    }

    return ['status' => 'success'];
  }

  public function get_workflow()
  {
  }

  public function get_workflows()
  {
    global $wpdb;
    $table_name = esc_sql($wpdb->prefix . 'workflows');

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name}"), OBJECT);

    if (!empty($results)) {
      return $results;
    } else {
      return null;
    }
  }

  public function create_workflow(\WP_REST_Request $request)
  {

    global $wpdb;
    $table_name = esc_sql($wpdb->prefix . $this->table_name);

    $data = $request->get_json_params();
    $name = sanitize_text_field($data['name']);
    $id = $data['id'];
    $module = $data['config']['selectedModule'];
    $action = $data['config']['selectedAction'];
    $config = maybe_serialize($data['config']);
    $action_data = maybe_serialize($data['actionData']);
    $status = 'active'; // Default status, modify as needed

    if ('new' === $id) {
      try {


    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->insert(
          $table_name,
          [
            'name' => $name,
            'module' => $module,
            'action' => $action,
            'config' => $config,
            'action_data' => $action_data,
            'status' => $status,
          ]
        );

        return new \WP_REST_Response(['message' => 'Workflow saved successfully'], 200);
      } catch (\Exception $error) {
        return new \WP_REST_Response(['message' => $error->getMessage()], 403);
      }
    } else {
      try {


    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->update(
          $table_name,
          [
            'name' => $name,
            'module' => $module,
            'action' => $action,
            'config' => $config,
            'action_data' => $action_data,
            'status' => $status,
          ],
          ['id' => absint($data['id'])],
          [
            '%s', // 'name'
            '%s', // 'module'
            '%s', // 'action'
            '%s', // 'config'
            '%s', // 'action_data'
            '%s', // 'status'
          ],
          ['%d'] // 'id' is treated as an integer
        );


        if ($wpdb->rows_affected) {
          return new \WP_REST_Response(['message' => 'Workflow updated successfully'], 200);
        } else {
          return new \WP_REST_Response(['message' => 'No record updated'], 200);
        }
      } catch (\Exception $error) {
        return new \WP_REST_Response(['message' => $error->getMessage()], 403);
      }
    }
  }

  public function update_workflow()
  {
  }

  // check permission
  public function get_permission()
  {
    return current_user_can('ndpv_workflow');
  }

  public function create_permission()
  {
    return current_user_can('ndpv_workflow');
  }
}
