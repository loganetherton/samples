<?php
namespace ValuePad\Api\Invitation\V2_0\Processors;

use ValuePad\Api\Support\Searchable\BaseSearchableProcessor;
use ValuePad\Api\Support\Searchable\SortableTrait;
use ValuePad\Core\Invitation\Enums\Status;
use ValuePad\Core\Invitation\Services\ResponderService;
use ValuePad\Core\Support\Criteria\Constraint;
use ValuePad\Core\Support\Criteria\Sorting\Sortable;

class InvitationsSearchableProcessor extends BaseSearchableProcessor
{
	use SortableTrait;

	protected function configuration()
	{
		return [
			'filter' => [
				'status' => [
					'constraint' => Constraint::IN,
					'type' => [['enum', Status::class]]
				],
				'licenseState' => Constraint::EQUAL,
				'licenseNumber' => Constraint::SIMILAR
			]
		];
	}

	/**
	 * @param Sortable $sortable
	 * @return bool
	 */
	protected function isResolvable(Sortable $sortable)
	{
		/**
		 * @var ResponderService $responderService
		 */
		$responderService = $this->container->make(ResponderService::class);

		return $responderService->canResolveSortable($sortable);
	}
}
