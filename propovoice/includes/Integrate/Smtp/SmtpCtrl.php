<?php
namespace Ndpv\Integrate\Smtp;

use Ndpv\Integrate\Smtp\SmtpList;

class SmtpCtrl {

	public function __construct() {
		new SmtpList();
	}
}
