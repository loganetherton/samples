<?php
namespace ValuePad\Api\Customer\V2_0\Processors;

use Ascope\Libraries\Validation\Rules\Enum;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Invitation\Enums\Requirement;
use ValuePad\Core\Invitation\Persistables\InvitationPersistable;

class InvitationsProcessor extends BaseProcessor
{
	protected function configuration()
	{
		return [
			'ascAppraiser' => 'int',
			'requirements' => [new Enum(Requirement::class)]
		];
	}

	/**
	 * @return InvitationPersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new InvitationPersistable());
	}
}
