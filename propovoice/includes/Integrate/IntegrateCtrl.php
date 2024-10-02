<?php
namespace Ndpv\Integrate;

use Ndpv\Integrate\Form\FormCtrl;
use Ndpv\Integrate\Smtp\SmtpCtrl;

class IntegrateCtrl {

    public function __construct() {
        new FormCtrl();
        new SmtpCtrl();
    }
}
