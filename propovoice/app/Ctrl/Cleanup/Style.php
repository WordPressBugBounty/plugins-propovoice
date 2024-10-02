<?php

namespace Ndpv\Ctrl\Cleanup;

use Ndpv\Traits\Singleton;

class Style
{
    use Singleton;

    public function init()
    {
        add_action('current_screen', [$this, 'get_plugin_screen']);

        if (!get_page_by_path('propovoice-form')) {
            $args = array(
                'post_title'    => 'Propovoice Form',
                'post_name'     => 'propovoice-form',
                'post_status'   => 'publish',
                'post_author'   => 1,
                'post_type'     => 'page',
            );
            $id = wp_insert_post($args);
            add_post_meta($id, '_wp_page_template', 'form-template.php');
        }
    }

    public function get_plugin_screen()
    {
        $current_screen = get_current_screen();

        if ($current_screen->id == 'toplevel_page_ndpv' && isset($_GET['page']) && $_GET['page'] === 'ndpv') {

            // remove admin notice
            add_action('in_admin_header', [$this, 'hide_notices'], 99);
            add_action('admin_print_styles', [$this, 'clear_styles_and_scripts'], 100);
        }
    }

    public function hide_notices()
    {
        remove_all_actions('user_admin_notices');
        remove_all_actions('admin_notices');
    }

    public function clear_styles_and_scripts()
    {
        global $wp_styles;
        $styles_to_keep = array("ndpv-dashboard", "ndpv-main", "ndpv-google-font", "admin-bar", "wp-auth-check", "plugin-installer-style", "colors");

        foreach ($wp_styles->queue as $handle) {

            if (in_array($handle, $styles_to_keep)) {
                continue;
            }
            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        }
    }
}
