<?php
/**
 * Propovoice
 *
 * @package    NurencyDigital - Propovoice
 * @author     NurencyDigital <support@propovoice.com>
 * @link       https://propovoice.com
 * @copyright  2024 Propovoice
 *
 * @wordpress-plugin
 * Plugin Name:       Propovoice
 * Plugin URI:        https://wordpress.org/plugins/propovoice
 * Description:       Lead, Deal, Estimate, Invoice, Billing, Client, Project Automation
 * Version:           1.7.6.4
 * Author:            Propovoice
 * Author URI:        https://propovoice.com
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Tested up to:      6.5
 * Text Domain:       propovoice
 * Domain Path:       /languages
 * License:           GPL3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * Don't allow call the file directly
 *
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || exit;

/**
 * Ndpv Final Class
 *
 * Here Ndpv means Nurency Digital Propovoice
 *
 * @since 1.0.0
 *
 * @class Ndpv The class that holds the entire Ndpv plugin
 */
final class Ndpv {

    /**
     * Instance of self
     *
     * @since 1.0.0
     *
     * @var WpPluginKit
     */
    private static $instance = null;

    /**
     * Plugin version
     *
     * @since 1.0.0
     *
     * @var string
     */
    private const VERSION = '1.7.6.4';

    /**
     * Holds various class instances.
     *
     * @var array
     *
     * @since 1.0.0
     */
    private $container = [];

    /**
     * Constructor for the Ndpv class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    public function __construct() {

        require_once __DIR__ . '/vendor/autoload.php';

        $this->define_constants();
        $this->init_plugin();
    }

    /**
     * Initializes the WpPluginKit() class
     *
     * Checks for an existing WpPluginKit() instance
     * and if it doesn't find one, create it.
     */
    public static function init() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @since 1.0.0
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @since 1.0.0
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset( $prop ) {
        return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
    }

    /**
     * Define all constants
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function define_constants() {
        define( 'NDPV_VERSION', self::VERSION );
        define( 'NDPV_FILE', __FILE__ );
        define( 'NDPV_SLUG', basename( dirname( NDPV_FILE ) ) );
        define( 'NDPV_DIR', __DIR__ );
        define( 'NDPV_PATH', plugin_dir_path( NDPV_FILE ) );
        define( 'NDPV_URL', plugins_url( '', NDPV_FILE ) );
        define( 'NDPV_TEMPLATE_DEBUG_MODE', false );
        define( 'NDPV_BUILD', NDPV_URL . '/build' );
        define( 'NDPV_ASSETS', NDPV_URL . '/assets' );
    }

    /**
     * Load the plugin after all plugins are loaded.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_plugin() {

        do_action( 'ndpv_before_init' );

        $this->includes();
        $this->init_hooks();

        // Fires after the plugin is loaded.
        do_action( 'ndpv_init' );
    }

    /**
     * Initialize the hooks.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function init_hooks() {
        // Localize our plugin
        add_action( 'init', [ $this, 'localization_setup' ] );
    }

    /**
     * Include the required files.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function includes() {

        if ( $this->is_request( 'admin' ) ) {
            $this->container['installer'] = new Ndpv\Setup\InstallCtrl();
            $this->container['admin_menu'] = new Ndpv\MenuPage\MenuPageCtrl();
            $this->container['assist'] = new Ndpv\Assist\AssistCtrl();
        }

        $this->container['rest_api'] = new Ndpv\Api\Controller();
        $this->container['assets'] = new Ndpv\Asset\Manager();
        $this->container['hooks'] = new Ndpv\Hook\HookCtrl();
        $this->container['template'] = new Ndpv\Template\TemplateCtrl();
        $this->container['integrate'] = new Ndpv\Integrate\IntegrateCtrl();
        $this->container['taxonomy'] = new Ndpv\Taxonomy\TaxonomyCtrl();
        $this->container['cron'] = new Ndpv\Cron\CronCtrl();
        $this->container['style'] = new Ndpv\Cleanup\Style();
    }

    /**
     * Initialize plugin for localization
     *
     * @since 1.0.0
     *
     * @uses load_plugin_textdomain()
     *
     * @return void
     */
    public function localization_setup() {
        load_plugin_textdomain( 'propovoice', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or public.
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function is_request( $type ) {
        switch ( $type ) {
            case 'admin':
                return is_admin();
            case 'public':
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
            case 'ajax':
                return defined( 'DOING_AJAX' );
            case 'cron':
                return defined( 'DOING_CRON' );
        }
    }

    /**
     * Get the plugin path.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( NDPV_FILE ) );
    }

    /**
     * Plugin version
     *
     * @since 1.0.0
     *
     * @var string
     */
    public function version() {
        return NDPV_VERSION;
    }

    /**
     * Get the template path.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_template_path() {
        return apply_filters( 'ndpv_template_path', 'ndpv/templates/' );
    }

    /**
     * Get the template partial path.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_partial_path( $path = null, $args = [] ) {
        Ndpv\Helpers\Fns::get_template_part( 'partial/' . $path, $args );
    }

    /**
     * Get asset uri depend on file
     *
     * @since 1.0.0
     *
     * @param $file
     *
     * @return string
     */
    public function get_asset_uri( $file ) {
        $file = ltrim( $file, '/' );

        return trailingslashit( NDPV_URL . '/assets' ) . $file;
    }

    /**
     * Render file from view folder
     *
     * @since 1.0.0
     *
     * @param $file
     *
     * @return void
     */
    public function render( $path, $args = [], $is_return = false ) {
        $path = str_replace( '.', '/', $path );
        $view_path = NDPV_PATH . '/view/' . $path . '.php';
        if ( ! file_exists( $view_path ) ) {
            return;
        }

        if ( $args ) {
            extract( $args );
        }

        if ( $is_return ) {
            ob_start();
            include $view_path;

            return ob_get_clean();
        }
        include $view_path;
    }

    /**
     * Get options field by args
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_options() {

        $option_field = func_get_args()[0];
        $result = get_option( $option_field );
        $func_args = func_get_args();
        array_shift( $func_args );

        foreach ( $func_args as $arg ) {
            if ( is_array( $arg ) ) {
                if ( ! empty( $result[ $arg[0] ] ) ) {
                    $result = $result[ $arg[0] ];
                } else {
                    $result = $arg[1];
                }
            } elseif ( ! empty( $result[ $arg ] ) ) {
                $result = $result[ $arg ];
            } else {
                $result = null;
            }
        }
        return $result;
    }

    /**
     * Get default value by args
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_default() {
        $data = new Ndpv\Helpers\Preset();
        $result = $data->default();
        $func_args = func_get_args();

        foreach ( $func_args as $arg ) {
            if ( is_array( $arg ) ) {
                if ( ! empty( $result[ $arg[0] ] ) ) {
                    $result = $result[ $arg[0] ];
                } else {
                    $result = $arg[1];
                }
            } elseif ( ! empty( $result[ $arg ] ) ) {
                $result = $result[ $arg ];
            } else {
                $result = null;
            }
        }
        return $result;
    }

    /**
     * Get default workspace id
     *
     * @since 1.0.0
     *
     * @return int|null
     */
    public function get_workspace() {
        $option = get_option( 'ndpv_workspace_default' );
        return $option ? absint( $option ) : null;
    }

    /**
     * Support plain permalink for rest api
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function plain_route() {
        $permalink_structure = get_option( 'permalink_structure' );
        return $permalink_structure === '' ? '/(?P<args>.*)' : '';
    }

    /**
     * Check Pro Version
     *
     * @since 1.0.0
     *
     * @return boolean
     */
    public function wage() {
        return function_exists( 'ndpvp' ) && ndpvp()->wage();
    }

    /**
     * Check Pro Version License Expired
     *
     * @since 1.0.0
     *
     * @return boolean
     */
    public function wagex() {
        return function_exists( 'ndpvp' ) && method_exists( ndpvp(), 'wagex' ) && ndpvp()->wagex();
    }

    /**
     * Check active module
     *
     * @since 1.0.0
     *
     * @return boolean
     */
    public function is_active_module() {
        return function_exists( 'ndpvp' ) && method_exists( ndpvp(), 'is_active_module' ) && ndpvp()->is_active_module();
    }
}

/**
 * Initialize the main plugin.
 *
 * @since 1.0.0
 *
 * @return Ndpv
 */
function ndpv() {
    return Ndpv::init();
}
ndpv(); // Run Ndpv Plugin
