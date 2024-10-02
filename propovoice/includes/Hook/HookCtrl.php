<?php

namespace Ndpv\Hook;

use Ndpv\Hook\Type\Filter;
use Ndpv\Hook\Type\Action\ActionCtrl;

class HookCtrl {

    public function __construct() {
        new Filter();
        new ActionCtrl();
    }
}
