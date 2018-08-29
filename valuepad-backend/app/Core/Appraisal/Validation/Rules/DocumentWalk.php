<?php
namespace ValuePad\Core\Appraisal\Validation\Rules;

use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\Walk;

class DocumentWalk extends Walk
{
	public function __construct()
	{
		parent::__construct(function(Binder $binder){

			foreach (['url', 'name', 'format'] as $field){

				$binder->bind($field, function(Property $property) use ($field){
					$property->addRule(new Obligate());
					$property->addRule(new Blank());
				});
			}

			$binder->bind('size', function(Property $property){
				$property
					->addRule(new Obligate())
					->addRule(new Greater(0, false));
			});
		});
	}
}
