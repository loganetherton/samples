<?php
namespace ValuePad\Core\Log\Enums;

use Ascope\Libraries\Enum\Enum;

class Action extends Enum
{
	const CREATE_ORDER = 'create-order';
	const UPDATE_ORDER = 'update-order';
	const DELETE_ORDER = 'delete-order';
	const BID_REQUEST = 'bid-request';
    const AWARD_ORDER = 'award-order';
	const UPDATE_PROCESS_STATUS = 'update-process-status';
	const CREATE_DOCUMENT = 'create-document';
	const UPDATE_DOCUMENT = 'update-document';
	const DELETE_DOCUMENT = 'delete-document';
	const CREATE_ADDITIONAL_DOCUMENT = 'create-additional-document';
	const DELETE_ADDITIONAL_DOCUMENT = 'delete-additional-document';
	const CHANGE_ADDITIONAL_STATUS = 'change-additional-status';
	const REVISION_REQUEST = 'revision-request';
	const RECONSIDERATION_REQUEST = 'reconsideration-request';
}
