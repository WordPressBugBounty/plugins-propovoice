<?php

require_once __DIR__ . './../vendor/autoload.php';

use Ndpv\Ctrl\Install\InstallCtrl;
use Ndpv\Traits\Singleton;
use Ndpv\Helper\Constant;
use Ndpv\Helper\Fns;
use Ndpv\Helper\Preset;
use Ndpv\Ctrl\MainCtrl;

/**
 * Class Ndpv
 */
final class Ndpv {

    use Singleton;

    /**
     * NDPV Project Constructor.
     */
    public function __construct() {
        new Constant();
        new InstallCtrl();
        $this->init_hooks();
    }

    private function init_hooks() {

        add_action('plugins_loaded', [$this, 'on_plugins_loaded'], -1);
        add_action('init', [$this, 'init'], 1);
    }

    public function init() {
        do_action('ndpv_before_init');

        $this->load_plugin_textdomain();

        new MainCtrl();

        do_action('ndpv_init');
    }

    public function on_plugins_loaded() {
        do_action('ndpv_loaded');
    }

    /**
     * Load Localization files.
     */
    public function load_plugin_textdomain() {

        $locale = determine_locale();
        $locale = apply_filters('ndpv_plugin_locale', $locale );
        unload_textdomain('propovoice');
        load_textdomain('propovoice', WP_LANG_DIR . '/ndpv/ndpv-' . $locale . '.mo');
        load_plugin_textdomain('propovoice', false, plugin_basename(dirname(NDPV_FILE)) . '/languages');
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or public.
     *
     * @return bool
     */
    public function is_request( $type ) {
        switch( $type ) {
            case 'admin':
                return is_admin();
            case 'public':
                return ( !is_admin() || defined('DOING_AJAX') ) && !defined('DOING_CRON');
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
        }
    }

    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit(plugin_dir_path(NDPV_FILE));
    }

    /**
     * @return mixed
     */
    public function version() {
        return NDPV_VERSION;
    }

    /**
     * Get the template path.
     *
     * @return string
     */
    public function get_template_path() {
        return apply_filters('ndpv_template_path', 'ndpv/templates/');
    }

    /**
     * Get the template partial path.
     *
     * @return string
     */
    public function get_partial_path( $path = null, $args = []) {
        Fns::get_template_part( 'partial/' . $path, $args );
    }

    /**
     * @param $file
     *
     * @return string
     */
    public function get_asset_uri($file) {
        $file = ltrim($file, '/');

        return trailingslashit(NDPV_URL . '/asset') . $file;
    }

    /**
     * @param $file
     *
     * @return string
     */
    public function render($viewName, $args = array(), $return = false) {
        $path = str_replace(".", "/", $viewName);
        $viewPath = NDPV_PATH . '/view/' . $path . '.php';
        if ( !file_exists($viewPath) ) {
            return;
        }

        if ( $args ) {
            extract($args);
        }

        if ( $return ) {
            ob_start();
            include $viewPath;

            return ob_get_clean();
        }
        include $viewPath;
    }

    /**
     * @param $file
     * Get all optoins field value
     * @return mixed
     */
    public function get_options() {

        $option_field = func_get_args()[0];
        $result = get_option( $option_field );
        $func_args = func_get_args();
        array_shift( $func_args );

        foreach ( $func_args as $arg ) {
            if ( is_array($arg) ) {
                if ( !empty( $result[$arg[0]] ) ) {
                    $result = $result[$arg[0]];
                } else {
                    $result = $arg[1];
                }
            } else {
                if ( !empty($result[$arg] ) ) {
                    $result = $result[$arg];
                } else {
                    $result = null;
                }
            }
        }
        return $result;
    }

    /**
     * @param $file
     * Get all optoins field value
     * @return mixed
     */
    public function get_default()
    {
        $data = new Preset;
        $result = $data->default();
        $func_args = func_get_args();

        foreach ($func_args as $arg) {
            if (is_array($arg)) {
                if (!empty($result[$arg[0]])) {
                    $result = $result[$arg[0]];
                } else {
                    $result = $arg[1];
                }
            } else {
                if (!empty($result[$arg])) {
                    $result = $result[$arg];
                } else {
                    $result = null;
                }
            }
        }
        return $result;
    }

    public function get_workspace() {
        $option = get_option( 'ndpv_workspace_default' );
        return $option ? absint( $option ) : null;
    }

    /**
     * For checking plain permalink
     * @return string
     */
    public function plain_route() {
        $permalink_structure = get_option('permalink_structure');
        return $permalink_structure === '' ? '/(?P<args>.*)' : '';
    }
}

/**
 * @return bool|Singleton|Ndpv
 */
function ndpv() {
    return Ndpv::getInstance();
}
ndpv(); // Run Ndpv Plugin
