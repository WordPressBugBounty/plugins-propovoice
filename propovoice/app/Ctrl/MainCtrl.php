<?php

namespace Ndpv\Ctrl;

use Ndpv\Ctrl\Api\ApiCtrl;
use Ndpv\Ctrl\Asset\AssetCtrl;
use Ndpv\Ctrl\Cron\CronCtrl;
use Ndpv\Ctrl\Template\TemplateCtrl;
use Ndpv\Ctrl\Hook\HookCtrl;
use Ndpv\Ctrl\Integrate\IntegrateCtrl;
use Ndpv\Ctrl\Assist\AssistCtrl;
use Ndpv\Ctrl\MenuPage\MenuPageCtrl;
use Ndpv\Ctrl\Taxonomy\TaxonomyCtrl;
use Ndpv\Ctrl\Widget\WidgetCtrl;
use Ndpv\Ctrl\Cleanup\Style;

class MainCtrl
{
    public function __construct()
    {
        new TaxonomyCtrl();
        new MenuPageCtrl();
        new AssistCtrl();
        new AssetCtrl();
        new TemplateCtrl();
        new WidgetCtrl();
        new HookCtrl();
        new ApiCtrl();
        new CronCtrl();
        new IntegrateCtrl();
        Style::getInstance()->init();
    }
}
