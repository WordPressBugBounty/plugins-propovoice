<?php

namespace Ndpv\Ctrl\Api;

use Ndpv\Ctrl\Api\Type\Action;
use Ndpv\Ctrl\Api\Type\Business;
use Ndpv\Ctrl\Api\Type\Client;
use Ndpv\Ctrl\Api\Type\Contact;
use Ndpv\Ctrl\Api\Type\Person;
use Ndpv\Ctrl\Api\Type\Dashboard;
use Ndpv\Ctrl\Api\Type\Deal;
use Ndpv\Ctrl\Api\Type\Email;
use Ndpv\Ctrl\Api\Type\File;
use Ndpv\Ctrl\Api\Type\Form;
use Ndpv\Ctrl\Api\Type\EstInv;
use Ndpv\Ctrl\Api\Type\Package;
use Ndpv\Ctrl\Api\Type\Order;
use Ndpv\Ctrl\Api\Type\Lead;
use Ndpv\Ctrl\Api\Type\Media;
use Ndpv\Ctrl\Api\Type\Note;
use Ndpv\Ctrl\Api\Type\Org;
use Ndpv\Ctrl\Api\Type\Parts\Workflow\Actions;
use Ndpv\Ctrl\Api\Type\Payment;
use Ndpv\Ctrl\Api\Type\PaymentProcess;
use Ndpv\Ctrl\Api\Type\Project;
use Ndpv\Ctrl\Api\Type\Request;
use Ndpv\Ctrl\Api\Type\Setting;
use Ndpv\Ctrl\Api\Type\Task;
use Ndpv\Ctrl\Api\Type\Taxonomy;
use Ndpv\Ctrl\Api\Type\Team;
use Ndpv\Ctrl\Api\Type\Webhook;
use Ndpv\Ctrl\Api\Type\SaveForNext;
use Ndpv\Ctrl\Api\Type\Workflow;

class ApiCtrl {

	public function __construct() {
		add_action(
			'rest_api_init',
			function () {
				Lead::getInstance()->register_routes();
				Deal::getInstance()->register_routes();
				Task::getInstance()->register_routes();
				Note::getInstance()->register_routes();
				File::getInstance()->register_routes();
				Client::getInstance()->register_routes();
				Person::getInstance()->register_routes();
				Org::getInstance()->register_routes();
				Contact::getInstance()->register_routes();
				Project::getInstance()->register_routes();
				EstInv::getInstance()->register_routes();
				Business::getInstance()->register_routes();
				Package::getInstance()->register_routes();
				Order::getInstance()->register_routes();
				Request::getInstance()->register_routes();
				Email::getInstance()->register_routes();
				Media::getInstance()->register_routes();
				Payment::getInstance()->register_routes();
				PaymentProcess::getInstance()->register_routes();
				Dashboard::getInstance()->register_routes();
				Action::getInstance()->register_routes();
				Taxonomy::getInstance()->register_routes();
				Form::getInstance()->register_routes();
				Webhook::getInstance()->register_routes();
				Setting::getInstance()->register_routes();
				Team::getInstance()->register_routes();
				SaveForNext::getInstance()->register_routes();
				Workflow::getInstance()->register_routes();
			}
		);

		// for plain permalink api support
		add_filter( 'rest_request_before_callbacks', array( $this, 'rest_request_filter' ), 10, 3 );
		Actions::getInstance()->execute();
	}

	public function rest_request_filter( $response, $handler, $request ) {
		$permalink_structure = get_option( 'permalink_structure' );
		if ( $permalink_structure === '' ) {
			$params = $request->get_params();
			if ( isset( $params['rest_route'] ) ) {
				$query_string = parse_url( $params['rest_route'], PHP_URL_QUERY );
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
