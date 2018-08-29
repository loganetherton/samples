<?php
namespace ValuePad\Api\Appraisal\V2_0\Processors;

use ValuePad\Api\Appraisal\V2_0\Support\MessageReaderResolver;
use ValuePad\Api\Support\Searchable\BaseSearchableProcessor;
use ValuePad\Api\Support\Searchable\SortableTrait;
use ValuePad\Core\Appraisal\Services\ResponderService;
use ValuePad\Core\Support\Criteria\Constraint;
use ValuePad\Core\Support\Criteria\Criteria;
use ValuePad\Core\Support\Criteria\Sorting\Sortable;

class MessagesSearchableProcessor extends BaseSearchableProcessor
{
	use SortableTrait;

	protected function configuration()
	{
		return [
			'filter' => [
				'isRead' => function($value){
					if (!in_array($value, ['true', 'false', true, false], true)){
						return null;
					}

					$constraint = new Constraint(Constraint::CONTAIN);

					if (in_array($value, [false, 'false'], true)){
						$constraint->setNot(true);
					}

                    /**
                     * @var MessageReaderResolver $readerResolver
                     */
					$readerResolver = $this->container->make(MessageReaderResolver::class);

					return new Criteria('readers', $constraint, $readerResolver->getReader());
				},
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

		return $responderService->canResolveMessageSortable($sortable);
	}
}
