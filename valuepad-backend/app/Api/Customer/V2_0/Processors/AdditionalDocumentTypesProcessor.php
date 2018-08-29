<?php
namespace ValuePad\Api\Customer\V2_0\Processors;

use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\StringCast;

class AdditionalDocumentTypesProcessor extends AbstractProcessor
{
	protected function rules(Binder $binder)
	{
		$binder->bind('title', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new StringCast())
				->addRule(new Blank())
				->addRule(new Length(1, 255));
		});
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->get('title');
	}
}
