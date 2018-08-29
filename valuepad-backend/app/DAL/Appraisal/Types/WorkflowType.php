<?php
namespace ValuePad\DAL\Appraisal\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use ValuePad\Core\Appraisal\Enums\Workflow;

class WorkflowType extends ProcessStatusType
{
	/**
	 * @return string
	 */
	protected function getEnumCollectionClass()
	{
		return Workflow::class;
	}

	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return 'TEXT';
	}

}
