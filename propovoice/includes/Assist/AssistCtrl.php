<?php
namespace Ndpv\Assist;

use Ndpv\Assist\Type\Feedback;
use Ndpv\Assist\Type\Link;

class AssistCtrl {

	public function __construct() {
		new Link();
		new Feedback();
	}
}
