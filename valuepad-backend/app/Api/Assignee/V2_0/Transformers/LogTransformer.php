<?php
namespace ValuePad\Api\Assignee\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Log\Entities\Log;
use ValuePad\Core\Log\Enums\Action;
use ValuePad\Core\Log\Extras\Extra;
use ValuePad\Core\Log\Extras\ExtraInterface;

class LogTransformer extends BaseTransformer
{
	private $actionLabels = [
		Action::CREATE_ORDER => 'New Order',
		Action::BID_REQUEST => 'Bid Request',
		Action::UPDATE_PROCESS_STATUS => 'Process Status Update',
		Action::DELETE_ORDER => 'Deleted Order',
		Action::UPDATE_ORDER => 'Updated Order',
		Action::AWARD_ORDER => 'Awarded Bid Request',
		Action::CREATE_DOCUMENT => 'Document Upload',
		Action::UPDATE_DOCUMENT => 'Document Updated',
		Action::DELETE_DOCUMENT => 'Document Delete',
		Action::CREATE_ADDITIONAL_DOCUMENT => 'Additional Document Upload',
		Action::DELETE_ADDITIONAL_DOCUMENT => 'Additional Document Delete',
		Action::CHANGE_ADDITIONAL_STATUS => 'Additional Status Changed',
		Action::RECONSIDERATION_REQUEST => 'Reconsideration Request',
		Action::REVISION_REQUEST => 'Revision Request',

	];

	/**
	 * @param Log $log
	 * @return array
	 */
	public function transform($log)
	{
		$data = $this->extract($log);

        $data['message'] = static::getMessage($log);
		$data['actionLabel'] = $this->actionLabels[(string) $log->getAction()];

		return $data;
	}

	public static function getMessage(Log $log)
	{
		$extra = $log->getExtra();
		$action = $log->getAction();


		if ($action->is(Action::BID_REQUEST)){
			return sprintf(
			    'You have received a bid request %s from %s.',
                static::getLocationWithClient($extra),
                $extra[Extra::CUSTOMER]
            );
		}

		if ($action->is(Action::REVISION_REQUEST)){
			return sprintf(
			    'You have received a revision request %s from %s.',
                static::getLocationWithClient($extra),
                $extra[Extra::CUSTOMER]
            );
		}

		if ($action->is(Action::RECONSIDERATION_REQUEST)){
			return sprintf(
			    'You have received a reconsideration request %s from %s.',
                static::getLocationWithClient($extra),
                $extra[Extra::CUSTOMER]
            );
		}

		if ($action->is(Action::CREATE_ORDER)){
			return sprintf(
			    'You have received a new order %s from %s.',
                static::getLocationWithClient($extra),
                $extra[Extra::CUSTOMER]
            );
		}

        if ($action->is(Action::AWARD_ORDER)){
            return sprintf(
                'You have been awarded the bid request %s from %s.',
                static::getLocationWithClient($extra),
                $extra[Extra::CUSTOMER]
            );
        }

		if ($action->is(Action::UPDATE_ORDER)){
			return sprintf('%s has updated the order %s.', $extra[Extra::USER], static::getLocationWithClient($extra));
		}

		if ($action->is(Action::DELETE_ORDER)){
			return sprintf('%s has deleted the order %s.', $extra[Extra::USER], static::getLocationWithClient($extra));
		}

		if ($action->is(Action::UPDATE_PROCESS_STATUS)){
			return sprintf(
				'%s has changed the process status to "%s".',
				$extra[Extra::USER],
				ucwords(str_replace(['-'], ' ', (string) $extra[Extra::NEW_PROCESS_STATUS]))
			);
		}

		if ($action->is(Action::CHANGE_ADDITIONAL_STATUS)){
			return sprintf(
				'%s has changed the additional status to "%s".',
				$extra[Extra::USER],
				$extra[Extra::NEW_ADDITIONAL_STATUS][Extra::TITLE]
			);
		}

		if ($action->is(Action::CREATE_DOCUMENT)){
			return sprintf(
				'%s has uploaded the "%s" document.',
				$extra[Extra::USER],
				$extra[Extra::NAME]
			);
		}

        if ($action->is(Action::UPDATE_DOCUMENT)){
            return sprintf(
                '%s has updated the "%s" document.',
                $extra[Extra::USER],
                $extra[Extra::NAME]
            );
        }

		if ($action->is(Action::DELETE_DOCUMENT)){
			return sprintf(
				'%s has deleted the "%s" document.',
				$extra[Extra::USER],
				$extra[Extra::NAME]
			);
		}

		if ($action->is(Action::CREATE_ADDITIONAL_DOCUMENT)){
			return sprintf(
				'%s has uploaded the "%s" additional document.',
				$extra[Extra::USER],
				$extra[Extra::NAME]
			);
		}

		if ($action->is(Action::DELETE_ADDITIONAL_DOCUMENT)){
			return sprintf(
				'%s has deleted the "%s" additional document.',
				$extra[Extra::USER],
				$extra[Extra::NAME]
			);
		}

		return null;
	}

	/**
	 * @param ExtraInterface $extra
	 * @return string
	 */
	private static function getLocationWithClient(ExtraInterface $extra)
	{
		return sprintf(
			'on %s, %s, %s %s',
			$extra[Extra::ADDRESS_1],
			$extra[Extra::CITY],
			$extra[Extra::STATE][Extra::CODE],
			$extra[Extra::ZIP]
		);
	}
}
