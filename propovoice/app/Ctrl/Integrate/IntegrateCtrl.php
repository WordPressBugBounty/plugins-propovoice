<?php 
namespace Ndpv\Ctrl\Integrate;

use Ndpv\Ctrl\Integrate\Form\FormCtrl;
use Ndpv\Ctrl\Integrate\Smtp\SmtpCtrl;

class IntegrateCtrl
{
    public function __construct()
    {
        new FormCtrl();
        new SmtpCtrl();
    } 
}
