<?php
namespace ValuePad\Api\Customer\V2_0\Processors;

use ValuePad\Api\Appraisal\V2_0\Support\AdditionalDocumentsConfigurationTrait;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Appraisal\Objects\Comparable;
use ValuePad\Core\Appraisal\Persistables\AdditionalDocumentPersistable;
use ValuePad\Core\Appraisal\Persistables\ReconsiderationPersistable;

class ReconsiderationsProcessor extends BaseProcessor
{
    use AdditionalDocumentsConfigurationTrait;

	protected function configuration()
	{
		return array_merge(
            [
                'comment' => 'string',
                'comparables' => [
                    'address' => 'string',
                    'salesPrice' => 'float',
                    'closedDate' => 'datetime',
                    'livingArea' => 'string',
                    'siteSize' => 'string',
                    'actualAge' => 'string',
                    'distanceToSubject' => 'string',
                    'sourceData' => 'string',
                    'comment' => 'string'
                ]
            ],
            $this->getAdditionalDocumentsConfiguration(['namespace' => 'document']),
            [
                'documents' => [
                    'type' => 'int',
                    'label' => 'string',
                    'document' => 'document'
                ]
            ]
        );
	}

	/**
	 * @return ReconsiderationPersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new ReconsiderationPersistable(), [
			'hint' => [
				'comparables' => 'collection:'.Comparable::class,
                'documents' => 'collection:'.AdditionalDocumentPersistable::class
			]
		]);
	}
}
