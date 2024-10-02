<?php 
namespace Ndpv\Ctrl\Integrate\Smtp;

use Ndpv\Ctrl\Integrate\Smtp\SmtpList; 

class SmtpCtrl
{ 
	public function __construct()
	{ 
		new SmtpList(); 
	}
}
