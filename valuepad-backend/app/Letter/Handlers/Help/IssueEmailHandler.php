<?php
namespace ValuePad\Letter\Handlers\Help;

class IssueEmailHandler extends HelpEmailHandler
{
	/**
	 * @return string
	 */
	protected function getSubject()
	{
		return 'ValuePad - Issue Reported';
	}
}
