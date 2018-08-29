<?php
namespace ValuePad\Debug\Controllers;

use ValuePad\Console\Support\Kernel as Artisan;
use ValuePad\Debug\Support\BaseController;


/**
 * The controller is used to trigger artisan command for resetting the project
 *
 *
 */
class ResetController extends BaseController
{
	/**
	 * @param Artisan $artisan
	 * @return string
	 */
	public function reset(Artisan $artisan)
	{
		$artisan->call('project:reset');
		return 'The project has been reset successfully!';
	}
}
