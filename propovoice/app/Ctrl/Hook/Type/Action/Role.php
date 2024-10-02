<?php

namespace Ndpv\Ctrl\Hook\Type\Action;

class Role
{
    public static $ndpv_caps = [
        "ndpv_core" => true,
        "ndpv_dashboard" => true,
        "ndpv_lead" => true,
        "ndpv_deal" => true,
        "ndpv_estimate" => true,
        "ndpv_invoice" => true,
        "ndpv_package" => true,
        "ndpv_order" => true,
        "ndpv_request" => true,
        "ndpv_client" => true,
        "ndpv_project" => true,
        "ndpv_action" => true,
        "ndpv_business" => true,
        "ndpv_contact" => true,
        "ndpv_email" => true,
        "ndpv_file" => true,
        "ndpv_form" => true,
        "ndvp_media" => true,
        "ndpv_note" => true,
        "ndpv_org" => true,
        "ndpv_payment" => true,
        "ndpv_person" => true,
        "ndpv_setting" => true,
        "npdv_workflows" => true,
        "ndpv_task" => true,
        "ndpv_taxonomy" => true,
        "ndpv_webhook" => true,
        "ndpv_workspace" => true,
        "ndpv_workflow" => true,
    ];

    public function __construct()
    {
        add_action("init", [$this, "update_admin_caps"], 11);
        add_action("init", [$this, "add_ndpv_role_to_admin"], 12);
    }

    public function update_admin_caps()
    {
        $admin_role = get_role("administrator");
        if ($admin_role->has_cap("ndpv_core")) return; // if already set return

        foreach (self::$ndpv_caps as $cap => $perm) {
            $admin_role->add_cap($cap, $perm);
        }
    }
  public function add_ndpv_role_to_admin()
    {
     // If user is not a admin nothing to do
      if(current_user_can('administrator')){
        // add ndpv_admin role for current user
        $current_user = wp_get_current_user();
        $current_user->add_role('ndpv_admin');
      }
    }
}
