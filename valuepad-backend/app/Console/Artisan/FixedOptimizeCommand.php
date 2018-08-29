<?php
namespace ValuePad\Console\Artisan;

use Illuminate\Foundation\Console\OptimizeCommand;

class FixedOptimizeCommand extends OptimizeCommand
{
	/**
	 * FIX: Laravel relies on hard coded paths but some folders like in symfony package are irrelevant
	 *
	 * @return array
	 */
	protected function getClassFiles()
	{
		return array_filter(parent::getClassFiles(), function($file){ return $file !== false; });
	}
}
