<?php
namespace ValuePad\Api\Customer\V2_0\Processors;

use Ascope\Libraries\Validation\Rules\Enum;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Customer\Enums\Criticality;
use ValuePad\Core\Customer\Persistables\SettingsPersistable;

class SettingsProcessor extends BaseProcessor
{
	protected function configuration()
	{
		return [
			'pushUrl' => 'string',
			'daysPriorInspectionDate' => 'int',
			'daysPriorEstimatedCompletionDate' => 'int',
			'preventViolationOfDateRestrictions' => new Enum(Criticality::class),
			'disallowChangeJobTypeFees' => 'bool',
			'showClientToAppraiser' => 'bool',
			'showDocumentsToAppraiser' => 'bool',
			'isSmsEnabled' => 'bool',
            'unacceptedReminder' => 'int',
            'removeAccountingData' => 'bool',
		];
	}

	/**
	 * @return SettingsPersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new SettingsPersistable());
	}
}
