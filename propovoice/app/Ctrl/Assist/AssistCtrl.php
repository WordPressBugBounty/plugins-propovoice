<?php 
namespace Ndpv\Ctrl\Assist;

use Ndpv\Ctrl\Assist\Type\Feedback;
use Ndpv\Ctrl\Assist\Type\Link;

class AssistCtrl {
	
	public function __construct() {   
		new Link(); 
		new Feedback();
	} 
}