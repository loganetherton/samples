<?php
namespace ValuePad\Letter\Handlers\Help;

class RequestFeatureEmailHandler extends HelpEmailHandler
{
	/**
	 * @return string
	 */
	protected function getSubject()
	{
		return ' ValuePad - Feature Requested';
	}
}
