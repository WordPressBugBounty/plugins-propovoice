<?php
namespace Ndpv\MenuPage;

use Ndpv\MenuPage\Type\Dashboard;
use Ndpv\MenuPage\Type\Welcome;

class MenuPageCtrl {

	public function __construct() {
		new Dashboard();
		new Welcome();
	}
}
