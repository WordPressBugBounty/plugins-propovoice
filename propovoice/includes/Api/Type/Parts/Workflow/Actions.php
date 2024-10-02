<?php

namespace Ndpv\Api\Type\Parts\Workflow;

use Ndpv\Traits\Singleton;

class Actions
{

  use Singleton;

  private $type;

  public function execute()
  {

    add_action('ndpvp_webhook', [$this, 'process'], 10, 2);
  }

  public function process($type, $data)
  {

    $this->type = $type;

    if ($type === 'lead_add') {
      $this->lead_add($data);
    }

    if ($type === 'deal_add') {
      $this->deal_add($data);
    }
    if ($type === 'lead_add_form') {
      $this->lead_add_form($data);
    }
    if ($type === 'lead_to_deal') {
      $this->lead_to_deal($data);
    }
    if ($type === 'staff_add') {
      $this->staff_add($data);
    }
  }

  private function staff_add($data)
  {
    if (isset($data['path'])) {
      $actions = $this->get_all_actions(ucwords($data['path']));
      if ($actions) {
        foreach ($actions as $action) {
          $config          = unserialize($action->config);
          $selected_action = $config['selectedAction'];
          $action_data     = unserialize($action->action_data);
          if ('Assign Team Member' === $selected_action) {
            $this->assign_team_member($action_data, $data);
          }
        }
      }
    }
  }

  private function assign_team_member($action_data, $data)
  {

    $user_ids[] = $action_data['member']['id'];
    update_post_meta($data['id'], '_ndpv_allowed_users', $user_ids);
  }

  private function lead_to_deal($data)
  {

    $lead_actions = $this->get_all_actions('Lead');
    if ($lead_actions) {
      foreach ($lead_actions as $lead_action) {
        $config          = unserialize($lead_action->config);
        $selected_action = $config['selectedAction'];
        $action_data     = unserialize($lead_action->action_data);
        if ('Send Mail' === $selected_action) {
          $this->send_mail($action_data, $data);
        }
      }
    }
  }

  private function lead_add_form($data)
  {

    $lead_actions = $this->get_all_actions('Lead');
    if ($lead_actions) {
      foreach ($lead_actions as $lead_action) {
        $config          = unserialize($lead_action->config);
        $selected_action = $config['selectedAction'];
        $action_data     = unserialize($lead_action->action_data);
        if ('Send Mail' === $selected_action) {
          $this->send_mail($action_data, $data);
        }
      }
    }
  }

  private function deal_add($data)
  {

    $lead_actions = $this->get_all_actions('Deal');
    if ($lead_actions) {
      foreach ($lead_actions as $lead_action) {
        $config          = unserialize($lead_action->config);
        $selected_action = $config['selectedAction'];
        $action_data     = unserialize($lead_action->action_data);
        if ('Send Mail' === $selected_action) {
          $this->send_mail($action_data, $data);
        }

        if ('Assign Team Member' === $selected_action) {
          $this->assign_team_member($action_data, $data);
        }
      }
    }
  }

  private function lead_add($data)
  {

    $lead_actions = $this->get_all_actions('Lead');
    if ($lead_actions) {
      foreach ($lead_actions as $lead_action) {
        $config          = unserialize($lead_action->config);
        $selected_action = $config['selectedAction'];
        $action_data     = unserialize($lead_action->action_data);
        if (!$action_data) {
          $action_data = $lead_action->action_data;
        }
        if ('Send Mail' === $selected_action) {
          $this->send_mail($action_data, $data);
        }

        if ('Assign Team Member' === $selected_action) {
          $this->assign_team_member($action_data, $data);
        }
        if ('Add Lead Label' === $selected_action) {
          $this->add_lead_label($action_data, $data);
        }
      }
    }
  }

  private function add_lead_label($action_data, $data)
  {
    wp_set_post_terms($data['id'], [$action_data], 'ndpv_lead_level');
  }

  private function send_mail($action_data, $data)
  {

    $email_address = (isset($data['email'])) ? $data['email'] : $data['data']['email'];

    $to      = $email_address; // Replace with the recipient's email address.
    $subject = $action_data['subject']; // Replace with your email subject.
    $message = $action_data['message']; // Replace with your email content.

    $headers = [
      'Content-Type: text/html; charset=UTF-8', // Use text/html for HTML emails.
    ];

    // Send the email.
    wp_mail($to, $subject, $message, $headers);
  }

  public function get_all_actions($type)
  {
    global $wpdb;

    $table_name = $wpdb->prefix . 'workflows'; // Assuming the table name is 'workflows' with the WordPress prefix

    // Prepare and execute the SQL query
    // $query = $wpdb->prepare(
    // 	"SELECT * FROM $table_name WHERE module = %s AND status = %s",
    // 	$type, // Replace 'module' with the actual value you're looking for
    // 	'active'
    // );


    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $results = $wpdb->get_results($wpdb->prepare( // db call ok
      "SELECT * FROM %s WHERE module = %s AND status = %s",
      $table_name,
      $type, // Replace 'module' with the actual value you're looking for
      'active'
    ), OBJECT);


    // Check if there are any results
    if ($results) {
      return $results;
    } else {
      // No results found
      return null;
    }
  }
}
