<?php
namespace ValuePad\Api\Appraisal\V2_0\Processors;

use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Each;
use Ascope\Libraries\Validation\Rules\IntegerCast;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Api\Support\Validation\Rules\TraversableCast;

class SelectedMessagesProcessor extends AbstractProcessor
{
	/**
	 * @param Binder $binder
	 */
	protected function rules(Binder $binder)
	{
		$binder->bind('messages', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Blank())
				->addRule(new TraversableCast())
				->addRule(new Each(function(){
					return new IntegerCast();
				}));
		});
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		$data = parent::toArray();

		$messages = array_take($data, 'messages');

		if ($messages === null){
			return $data;
		}

		if (is_string($messages)){
			$messages = array_map(function($v){ return (int) $v; }, explode(',', $messages));
		}

		$data['messages'] = $messages;

		return $data;
	}

	/**
	 * @return array
	 */
	protected function allowable()
	{
		return ['messages'];
	}

	/**
	 * @return array
	 */
	public function getMessages()
	{
		return $this->get('messages');
	}
}
