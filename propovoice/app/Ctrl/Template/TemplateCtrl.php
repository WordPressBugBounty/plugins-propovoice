<?php

namespace Ndpv\Ctrl\Template;

class TemplateCtrl
{
    public function __construct()
    {
        add_filter("theme_page_templates", [$this, "template_list"], 10, 4);
        add_filter("template_include", [$this, "template_path"]);
        add_action("wp_enqueue_scripts", [$this, "wage_scripts"], 9999);
        add_action("admin_enqueue_scripts", [$this, "wage_scripts"], 9999);
    }

    /**
     * Add "Custom" template to page attirbute template section.
     */
    function template_list($post_templates, $wp_theme, $post, $post_type)
    {
        if (function_exists("ndpvp")) {
            $post_templates["workspace-template.php"] = esc_html__(
                "Propovoice Workspace",
                "propovoice"
            );
            $post_templates["form-template.php"] = esc_html__(
                "Propovoice Embedded Form",
                "propovoice"
            );
            /* $post_templates["package-template.php"] = esc_html__(
                "Propovoice Client Package",
                "propovoice"
            ); */
        }
        $post_templates["estimate-template.php"] = esc_html__(
            "Propovoice Client Estimate",
            "propovoice"
        );
        $post_templates["invoice-template.php"] = esc_html__(
            "Propovoice Client Invoice",
            "propovoice"
        );
        return $post_templates;
    }

    /**
     * Check if current page has our custom template. Try to load
     * template from theme directory and if not exist load it
     * from root plugin directory.
     * @since 1.0.0
     */
    function template_path($default)
    {
        $templates = ["workspace", "invoice", "estimate", "package", "form"];

        foreach ($templates as $template) {
            if (get_page_template_slug() === $template . "-template.php") {
                $custom_template =
                    ndpv()->plugin_path() .
                    "/view/template/" .
                    $template .
                    "-template.php";
                if (file_exists($custom_template)) {
                    return $custom_template;
                    break;
                }
            }
        }

        return $default;
    }

    /**
     *  Check wage script
     *
     * @since 1.0.0
     */
    function wage_scripts()
    {
        wp_localize_script(
            "ndpv-dashboard",
            "wage",
            apply_filters("ndpv_wage", ["PT97"])
        );
        wp_localize_script(
            "ndpv-dashboard",
            "wagex",
            [
                'exp' => ndpv()->wagex()
            ]
        );
        wp_localize_script(
            "ndpv-invoice",
            "wage",
            apply_filters("ndpv_wage", ["PT97"])
        );
        wp_localize_script(
            "ndpv-package",
            "wage",
            apply_filters("ndpv_wage", ["PT97"])
        );
        wp_localize_script("ndpv-dashboard", "has_wage", [
            "ins" => function_exists("ndpvp"),
        ]);
    }
}
