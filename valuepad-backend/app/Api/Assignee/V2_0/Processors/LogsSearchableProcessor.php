<?php
namespace ValuePad\Api\Assignee\V2_0\Processors;

use ValuePad\Api\Support\Searchable\BaseSearchableProcessor;
use ValuePad\Api\Support\Searchable\SortableTrait;
use ValuePad\Core\Log\Services\ResponderService;
use ValuePad\Core\Session\Entities\Session;
use ValuePad\Core\Support\Criteria\Constraint;
use ValuePad\Core\Support\Criteria\Criteria;
use ValuePad\Core\Support\Criteria\Sorting\Sortable;

class LogsSearchableProcessor extends BaseSearchableProcessor
{
	use SortableTrait;

	protected function configuration()
	{
		return [
			'filter' => [
				'initiator' => function($value){
					if (!in_array($value, ['false', false], true)){
						return null;
					}

					/**
					 * @var Session $session
					 */
					$session = $this->container->make(Session::class);

					return new Criteria('initiator', new Constraint(Constraint::EQUAL, true), $session->getUser());
				}
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

		return  $responderService->canResolveSortable($sortable);
	}
}
