<?php

namespace Ndpv\Api;

use Ndpv\Api\Type\Action;
use Ndpv\Api\Type\Business;
use Ndpv\Api\Type\Client;
use Ndpv\Api\Type\Contact;
use Ndpv\Api\Type\Person;
use Ndpv\Api\Type\Dashboard;
use Ndpv\Api\Type\Deal;
use Ndpv\Api\Type\Email;
use Ndpv\Api\Type\File;
use Ndpv\Api\Type\Form;
use Ndpv\Api\Type\EstInv;
use Ndpv\Api\Type\Package;
use Ndpv\Api\Type\Order;
use Ndpv\Api\Type\Lead;
use Ndpv\Api\Type\Media;
use Ndpv\Api\Type\Note;
use Ndpv\Api\Type\Org;
use Ndpv\Api\Type\Parts\Workflow\Actions;
use Ndpv\Api\Type\Payment;
use Ndpv\Api\Type\PaymentProcess;
use Ndpv\Api\Type\Project;
use Ndpv\Api\Type\Request;
use Ndpv\Api\Type\Setting;
use Ndpv\Api\Type\Task;
use Ndpv\Api\Type\Taxonomy;
use Ndpv\Api\Type\Team;
use Ndpv\Api\Type\Webhook;
use Ndpv\Api\Type\SaveForNext;
use Ndpv\Api\Type\Workflow;


/**
 * Class Api Controller
 *
 * Controller for registering custom REST API endpoints.
 *
 * @since 1.0.0
 */
class Controller {

	/**
     * Class dir and class name mapping.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected $class_map;
  private array $controllers = [];

	public function __construct() {

		// Register custom REST API endpoints
        if ( ! class_exists( 'WP_REST_Server' ) ) {
            return;
        }

        $this->class_map = apply_filters(
            'ndpv_rest_api_class_map',
            [
                Lead::class,
				Deal::class,
				Task::class,
				Note::class,
				File::class,
				Client::class,
				Person::class,
				Org::class,
				Contact::class,
				Project::class,
				EstInv::class,
				Business::class,
				Package::class,
				Order::class,
				Request::class,
				Email::class,
				Media::class,
				Payment::class,
				PaymentProcess::class,
				Dashboard::class,
				Action::class,
				Taxonomy::class,
				Form::class,
				Webhook::class,
				Team::class,
				SaveForNext::class,
				Workflow::class,
				Setting::class,
            ]
        );

        // Init REST API routes.
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ], 10 );

		Actions::init()->execute();

		// For plain permalink api support
		add_filter( 'rest_request_before_callbacks', [ $this, 'rest_request_filter' ], 10, 3 );
	}

	/**
     * Register REST API routes.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_rest_routes(): void {
        foreach ( $this->class_map as $controller_class ) {
            // $this->$controller = new $controller();
            // $this->$controller->routes();

          $controller_instance = new $controller_class();
          $this->controllers[$controller_class] = $controller_instance;
          $controller_instance->routes();
        }
    }
	/**
     * Support plain permalink for rest api
     *
     * @since 1.0.0
     *
     * @return void
     */
	public function rest_request_filter( $response, $handler, $request ) {
		$permalink_structure = get_option( 'permalink_structure' );
		if ( $permalink_structure === '' ) {
			$params = $request->get_params();
			if ( isset( $params['rest_route'] ) ) {
				$query_string = wp_parse_url( $params['rest_route'], PHP_URL_QUERY );
				// Parse the query string into an array of parameters
				parse_str( $query_string, $param_form_args );
				foreach ( $param_form_args as $key => $val ) {
					$request->set_param( $key, $val );
				}
			}
		}
		return $request;
	}
}
