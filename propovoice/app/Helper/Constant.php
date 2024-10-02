<?php

namespace Ndpv\Helper;

class Constant
{
    public function __construct()
    {
        if (!defined('NDPV_VERSION')) {
            define('NDPV_VERSION', '1.7.5.3');
        }

        if (!defined('NDPV_PATH')) {
            define('NDPV_PATH', plugin_dir_path(NDPV_FILE));
        }

        if (!defined('NDPV_URL')) {
            define('NDPV_URL', plugins_url('', NDPV_FILE));
        }

        if (!defined('NDPV_SLUG')) {
            define('NDPV_SLUG', basename(dirname(NDPV_FILE)));
        }

        if (!defined('NDPV_TEMPLATE_DEBUG_MODE')) {
            define('NDPV_TEMPLATE_DEBUG_MODE', false);
        }
    }
}
