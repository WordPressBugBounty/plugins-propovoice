<?php

namespace Ndpv\Ctrl\Asset;

use Ndpv\Helper\Fns;
use Ndpv\Helper\I18n;

class AssetCtrl
{
    private $suffix;
    private $version;
    public $current_user_caps;

    public function __construct()
    {
        $this->suffix = defined("SCRIPT_DEBUG") && SCRIPT_DEBUG ? "" : ".min";
        $this->version = defined("WP_DEBUG") && WP_DEBUG ? time() : ndpv()->version();
        $this->current_user_caps = array_keys(
            array_filter(wp_get_current_user()->allcaps)
        );

        add_action("wp_enqueue_scripts", [$this, "public_scripts"], 9999);
        add_action("admin_enqueue_scripts", [$this, "admin_scripts"], 9999);

        //remove thank you text from propovoice dashboard
        if (isset($_GET["page"]) && $_GET["page"] == "ndpv") {
            add_filter("admin_footer_text", "__return_empty_string", 11);
            add_filter("update_footer", "__return_empty_string", 11);
        }

        add_filter("show_admin_bar", [$this, "hide_admin_bar"]);

        add_action("current_screen", function () {
            if (!$this->is_plugins_screen()) {
                return;
            }

            add_action("admin_enqueue_scripts", [
                $this,
                "enqueue_feedback_dialog",
            ]);
        });
    }

    public function hide_admin_bar($show)
    {
        if (
            is_page_template([
                "workspace-template.php",
                "invoice-template.php",
                "estimate-template.php",
                "package-template.php",
                "form-template.php",
            ])
        ) {
            return false;
        }
        return $show;
    }

    private function admin_public_script()
    {
    }

    private function dashboard_script()
    {
        //font family
        if (
            (isset($_GET["page"]) && $_GET["page"] == "ndpv-welcome") ||
            (isset($_GET["page"]) && $_GET["page"] == "ndpv") ||
            is_page_template([
                "workspace-template.php",
                "invoice-template.php",
                "estimate-template.php",
                "package-template.php",
            ]) ||
            $this->is_plugins_screen()
        ) {
            wp_enqueue_style(
                "ndpv-google-font",
                "https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap",
                [],
                $this->version
            );
            wp_enqueue_style(
                "ndpv-main",
                ndpv()->get_asset_uri("css/main{$this->suffix}.css"),
                [],
                $this->version
            );
        }
        if (isset($_GET["page"]) && $_GET["page"] == "ndpv-welcome") {
            wp_enqueue_style(
                "ndpv-welcome",
                ndpv()->get_asset_uri("css/welcome{$this->suffix}.css"),
                [],
                $this->version
            );
            wp_enqueue_script(
                "ndpv-welcome",
                ndpv()->get_asset_uri("/js/welcome{$this->suffix}.js"),
                [],
                $this->version,
                true
            );
            wp_localize_script("ndpv-welcome", "ndpv", [
                "apiUrl" => esc_url(rest_url()),
                "nonce" => wp_create_nonce("wp_rest"),
                "dashboard" => menu_page_url("ndpv", false),
                "assetImgUri" => ndpv()->get_asset_uri("img/"),
                "logo" => Fns::brand_logo(),
                "i18n" => I18n::dashboard(),
            ]);
        }

        if (
            is_page_template(["invoice-template.php", "estimate-template.php"])
        ) {
            wp_enqueue_script(
                "ndpv-invoice",
                ndpv()->get_asset_uri("/js/estinv{$this->suffix}.js"),
                [],
                $this->version,
                true
            );
        }

        if (
            is_page_template(["package-template.php"])
        ) {
            wp_enqueue_script(
                "ndpv-package",
                ndpv()->get_asset_uri("/js/package{$this->suffix}.js"),
                [],
                $this->version,
                true
            );
        }

        if (
            is_page_template(["invoice-template.php", "estimate-template.php", "package-template.php"])
        ) {
            $local = [
                "apiUrl" => esc_url(rest_url()),
                "assetUri" => trailingslashit(NDPV_URL),
                "nonce" => wp_create_nonce("wp_rest"),
                "date_format" => Fns::phpToMomentFormat(
                    get_option("date_format")
                ),
                "assetImgUri" => ndpv()->get_asset_uri("img/"),
                "i18n" => I18n::dashboard()
            ];
            wp_localize_script("ndpv-invoice", "ndpv", $local);
            wp_localize_script("ndpv-package", "ndpv", $local );
        }

        if (
            (isset($_GET["page"]) && $_GET["page"] == "ndpv") ||
            is_page_template([
                "workspace-template.php",
                "invoice-template.php",
                "estimate-template.php",
                "package-template.php",
            ])
        ) {
            wp_enqueue_style(
                "ndpv-dashboard",
                ndpv()->get_asset_uri("css/dashboard{$this->suffix}.css"),
                [],
                $this->version
            );
        }
        if (
            (isset($_GET["page"]) && $_GET["page"] == "ndpv") ||
            is_page_template([
                "workspace-template.php"
            ])
        ) {
            wp_enqueue_script(
                "ndpv-dashboard",
                ndpv()->get_asset_uri("/js/dashboard{$this->suffix}.js"),
                [],
                $this->version,
                true
            );
            $current_user = wp_get_current_user();
            $segment = (get_option('permalink_structure') === '' ? '&' : '?'); //if plain permalink
            wp_localize_script("ndpv-dashboard", "ndpv", [
                "apiUrl" => esc_url(rest_url()),
                "siteUrl" => get_site_url(),
                "version" => ndpv()->version(),
                "dashboard" => admin_url("admin.php?page=ndpv"),
                "invoice_page_url" => sprintf(
                    "%s%sid=%s&token=%s",
                    Fns::client_page_url("invoice"),
                    $segment,
                    "invoice_id",
                    "invoice_token"
                ),
                "estimate_page_url" => sprintf(
                    "%s%sid=%s&token=%s",
                    Fns::client_page_url("estimate"),
                    $segment,
                    "invoice_id",
                    "invoice_token"
                ),
                "package_page_url" => sprintf(
                    "%s%sid=%s&token=%s",
                    Fns::client_page_url("package"),
                    $segment,
                    "package_id",
                    "package_token"
                ),
                "nonce" => wp_create_nonce("wp_rest"),
                "date_format" => Fns::phpToMomentFormat(
                    get_option("date_format")
                ),
                "assetImgUri" => ndpv()->get_asset_uri("img/"),
                "logo" => Fns::brand_logo(),
                "assetUri" => trailingslashit(NDPV_URL),
                "profile" => [
                    "id" => $current_user->ID,
                    "name" => $current_user->display_name,
                    "email" => $current_user->user_email,
                    "img" => get_avatar_url($current_user->ID, [
                        "size" => "36",
                    ]),
                    "logout" => wp_logout_url(get_permalink()),
                ],
                "i18n" => I18n::dashboard(),
                "is_service_workflow" => ndpv()->is_active_module(),
                "caps" => $this->current_user_caps,
                "isDemo" => apply_filters("ndpv_demo", false),
                "demoMsg" => "You are not allowed to change settings in demo mode!",
            ]);
        }
    }

    public function public_scripts()
    {
        $this->admin_public_script();

        //wp_enqueue_style( 'propovoice-main', ndpv()->get_asset_uri( "public/css/main{$this->suffix}.css" ), array(), $this->version );

        $this->dashboard_script();
    }

    public function admin_scripts()
    {
        $this->admin_public_script();
        $this->dashboard_script();
    }

    /**
     * Enqueue feedback dialog scripts.
     *
     * Registers the feedback dialog scripts and enqueues them.
     *
     * @since 1.0.0
     * @access public
     */
    public function enqueue_feedback_dialog()
    {
        add_action("admin_footer", [$this, "deactivate_feedback_dialog"]);
        wp_enqueue_script(
            "ndpv-feedback",
            ndpv()->get_asset_uri("/js/feedback{$this->suffix}.js"),
            [],
            $this->version,
            true
        );
        wp_localize_script("ndpv-feedback", "ndpv", [
            "ajaxurl" => esc_url(admin_url("admin-ajax.php")),
        ]);
    }

    /**
     * @since 1.0.1.5
     */
    public function deactivate_feedback_dialog()
    {
        ndpv()->render("feedback/form");
    }

    /**
     * @since 1.0.1.5
     */
    private function is_plugins_screen()
    {
        if (!function_exists("get_current_screen")) {
            require_once ABSPATH . "/wp-admin/includes/screen.php";
        }

        if (is_admin()) {
            return in_array(get_current_screen()->id, [
                "plugins",
                "plugins-network",
            ]);
        } else {
            return false;
        }
    }
}
