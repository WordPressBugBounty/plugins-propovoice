<?php
namespace Ndpv\Ctrl\MenuPage;

use Ndpv\Ctrl\MenuPage\Type\Dashboard;
use Ndpv\Ctrl\MenuPage\Type\Welcome;

class MenuPageCtrl {

	public function __construct() {
		if ( is_admin() ) {
			new Dashboard();
			new Welcome();
		}
	}
}