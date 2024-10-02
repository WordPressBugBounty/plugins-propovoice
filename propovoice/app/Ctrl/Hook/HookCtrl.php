<?php

namespace Ndpv\Ctrl\Hook;

use Ndpv\Ctrl\Hook\Type\Filter;
use Ndpv\Ctrl\Hook\Type\Action\ActionCtrl;

class HookCtrl
{
    public function __construct()
    {
        new Filter();
        new ActionCtrl();
    }
}
