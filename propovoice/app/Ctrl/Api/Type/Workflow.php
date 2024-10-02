<?php

namespace Ndpv\Ctrl\Api\Type;

use Ndpv\Traits\Singleton;

class Workflow
{


	use Singleton;

	private $path       = '/workflows';
	private $table_name = 'workflows';

	public function __construct()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_name;

		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			// SQL to create workflows table
			$charset_collate = $wpdb->get_charset_collate();
			$sql             = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        module tinytext NOT NULL,
        action tinytext NOT NULL,
        config text NOT NULL,
        action_data text NOT NULL,
        status tinytext NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta($sql);
		}
	}

	public function register_routes()
	{
		// Get specific workflow.
		register_rest_route(
			'ndpv/v1',
			$this->path . '/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array($this, 'get_workflow'),
				'permission_callback' => array($this, 'get_permission'),
				'args'                => array(
					'id' => array(
						'validate_callback' => function ($param) {
							return is_numeric($param);
						},
					),
				),
			)
		);

		// get all workflows.
		register_rest_route(
			'ndpv/v1',
			$this->path,
			array(
				'methods'             => 'GET',
				'callback'            => array($this, 'get_workflows'),
				'permission_callback' => array($this, 'get_permission'),
			)
		);

		// create new workflow.
		register_rest_route(
			'ndpv/v1',
			$this->path,
			array(
				'methods'             => 'POST',
				'callback'            => array($this, 'create_workflow'),
				'permission_callback' => array($this, 'create_permission'),
			)
		);

		// delete workflow.
		register_rest_route(
			'ndpv/v1',
			$this->path . '/delete',
			array(
				'methods'             => 'POST',
				'callback'            => array($this, 'delete_workflow'),
				'permission_callback' => array($this, 'create_permission'),
			)
		);

		// update a workflow.

		register_rest_route(
			'ndpv/v1',
			$this->path,
			array(
				'methods'             => 'POST',
				'callback'            => array($this, 'update_workflow'),
				'permission_callback' => array($this, 'create_permission'),
			)
		);
	}


	public function delete_workflow($request)
	{

		global $wpdb;
		$id = $request->get_param('id');

		$table_name = $wpdb->prefix . $this->table_name;
		$sql = '';
		// Prepare the SQL query
		if ($id) {
			$sql = $wpdb->prepare("DELETE FROM $table_name WHERE id = %d", $id);
		} else {
			
			$ids = array();
			foreach ($request->get_params() as $i => $id) {
				array_push($ids, $id);
			}

			// Generate placeholders for each ID
			$placeholders = array_fill(0, count($ids), '%d');
			$placeholders = implode(', ', $placeholders);

			// Prepare the SQL query with multiple placeholders
			$sql = $wpdb->prepare("DELETE FROM $table_name WHERE id IN ($placeholders)", $ids);
		}


		// Execute the query
		$wpdb->query($sql);
		return array('status' => 'success');
	}
	public function get_workflow()
	{
	}

	public function get_workflows()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'workflows';

		$results = $wpdb->get_results("SELECT * FROM {$table_name}", OBJECT);

		if (!empty($results)) {
			return $results;
		} else {
			return null;
		}
	}

	public function create_workflow(\WP_REST_Request $request)
	{

		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_name;

		$data        = $request->get_json_params();
		$name        = sanitize_text_field($data['name']);
		$id          = $data['id'];
		$module      = $data['config']['selectedModule'];
		$action      = $data['config']['selectedAction'];
		$config      = maybe_serialize($data['config']);
		$action_data = maybe_serialize($data['actionData']);
		$status      = 'active'; // Default status, modify as needed

		if ('new' === $id) {
			try {
				$wpdb->insert(
					$table_name,
					array(
						'name'        => $name,
						'module'      => $module,
						'action'      => $action,
						'config'      => $config,
						'action_data' => $action_data,
						'status'      => $status,
					)
				);
				return new \WP_REST_Response(array('message' => 'Workflow saved successfully'), 200);
			} catch (\Exception $error) {
				return new \WP_REST_Response(array('message' => $error->getMessage()), 403);
			}
		} else {
			try {
				$wpdb->update(
					$table_name,
					array(
						'name'        => $name,
						'module'      => $module,
						'action'      => $action,
						'config'      => $config,
						'action_data' => $action_data,
						'status'      => $status,
					),
					array('id' => absint($data['id'])),
					array(
						'%s', // 'name'
						'%s', // 'module'
						'%s', // 'action'
						'%s', // 'config'
						'%s', // 'action_data'
						'%s', // 'status'
					),
					array('%d') // 'id' is treated as an integer
				);

				if ($wpdb->rows_affected) {
					return new \WP_REST_Response(array('message' => 'Workflow updated successfully'), 200);
				} else {
					return new \WP_REST_Response(array('message' => 'No record updated'), 200);
				}
			} catch (\Exception $error) {
				return new \WP_REST_Response(array('message' => $error->getMessage()), 403);
			}
		}
	}

	public function update_workflow()
	{
	}

	// check permission
	public function get_permission()
	{
		return current_user_can("ndpv_workflow");
	}

	public function create_permission()
	{
		return current_user_can("ndpv_workflow");
	}
}
