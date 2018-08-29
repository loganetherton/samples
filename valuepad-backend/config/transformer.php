<?php
$config = [
    'include' => [
        'default' => [
            ValuePad\Core\User\Entities\User::class => [
                'id', 'firstName', 'lastName', 'fullName', 'username', 'email', 'displayName', 'type'
            ],
			ValuePad\Core\Customer\Entities\Customer::class => [
				'name', 'companyType'
			],
			ValuePad\Core\User\Entities\System::class => [
				'name'
			],
			ValuePad\Core\Location\Entities\County::class => [
				'id', 'title'
			],
			ValuePad\Core\Appraisal\Entities\Order::class => [
				'id', 'fileNumber'
			],
			ValuePad\Core\Amc\Entities\Amc::class => [
				'companyName'
			],
			ValuePad\Core\Company\Entities\Company::class => [
				'id', 'name'
			],
			ValuePad\Core\Company\Entities\Branch::class => [
				'id', 'name'
			],
            ValuePad\Core\Company\Entities\Staff::class => [
                'id', 'user', 'email', 'phone', 'isAdmin', 'isManager', 'isRfpManager'
            ],
            ValuePad\Core\Appraisal\Entities\Bid::class => [
            	'id', 'amount', 'estimatedCompletionDate', 'comments'
            ]
        ],
        'ignore' => [
            ValuePad\Core\User\Entities\User::class => [
                'password'
            ],
            ValuePad\Core\Document\Entities\Document::class => [
				'uri', 'isExternal'
            ],
			ValuePad\Core\Appraiser\Entities\License::class => [
				'appraiser'
			],
			ValuePad\Core\Amc\Entities\License::class => [
				'amc'
			],
			ValuePad\Core\Customer\Entities\Customer::class => [
				'appraisers', 'amcs'
			],
			ValuePad\Core\Appraiser\Entities\Appraiser::class => [
				'customers', 'licenses', 'ach'
			],
			ValuePad\Core\Amc\Entities\Amc::class => [
				'customers', 'settings'
			],
			ValuePad\Core\Assignee\Entities\CustomerFee::class => [
				'assignee', 'customer'
			],
			ValuePad\Core\Appraiser\Entities\DefaultFee::class => [
				'appraiser'
			],
			ValuePad\Core\Appraisal\Entities\Order::class => [
				'workflow',
				'hasAdditionalDocuments',
				'hasInstructionDocuments',
				'borrower',
                'supportingDetails',
                'staff'
			],
			ValuePad\Core\Customer\Entities\JobType::class => [
				'customer', 'isHidden'
			],
			ValuePad\Core\Customer\Entities\DocumentSupportedFormats::class => [
				'customer'
			],
			ValuePad\Core\Appraisal\Entities\Document::class => [
				'order'
			],
			ValuePad\Core\Appraisal\Entities\AdditionalDocument::class => [
				'order'
			],
			ValuePad\Core\Appraisal\Entities\Revision::class => [
				'order'
			],
			ValuePad\Core\Appraisal\Entities\Reconsideration::class => [
				'order'
			],
			ValuePad\Core\Appraisal\Entities\Property::class => [
				'id', 'hasContacts'
			],
			ValuePad\Core\Appraiser\Entities\EoEx::class => [
				'id'
			],
			ValuePad\Core\Appraiser\Entities\Qualifications::class => [
				'id'
			],
			ValuePad\Core\Appraiser\Entities\Ach::class => [
				'id', 'appraiser'
			],
			ValuePad\Core\Appraisal\Entities\Bid::class => [
				'id', 'order'
			],
			ValuePad\Core\Log\Entities\Log::class => [
				'assignee', 'customer'
			],
			ValuePad\Core\Appraisal\Entities\Message::class => [
				'readers'
			],
			ValuePad\Core\Customer\Entities\AdditionalStatus::class => [
				'customer', 'isActive'
			],
			ValuePad\Core\Shared\Entities\Availability::class => [
				'id'
			],
			ValuePad\Core\Assignee\Entities\NotificationSubscription::class => [
				'assignee', 'id'
			],
			ValuePad\Core\Customer\Entities\Settings::class => [
				'customer'
			],
			ValuePad\Core\Appraisal\Entities\AcceptedConditions::class => [
				'id'
			],
			ValuePad\Core\User\Entities\Device::class => [
				'user'
			],
			ValuePad\Core\Customer\Entities\Client::class => [
				'customer'
			],
			ValuePad\Core\Customer\Entities\Ruleset::class => [
				'customer'
			],
			ValuePad\Core\Amc\Entities\Fee::class => [
				'amc', 'isEnabled', 'id'
			],
			ValuePad\Core\Amc\Entities\FeeByState::class => [
				'fee', 'id'
			],
			ValuePad\Core\Amc\Entities\FeeByCounty::class => [
				'fee', 'id'
			],
			ValuePad\Core\Amc\Entities\FeeByZip::class => [
				'fee', 'id'
			],
			ValuePad\Core\Amc\Entities\CustomerFeeByState::class => [
				'fee', 'id'
			],
			ValuePad\Core\Amc\Entities\CustomerFeeByCounty::class => [
				'fee', 'id'
			],
			ValuePad\Core\Amc\Entities\CustomerFeeByZip::class => [
				'fee', 'id'
			],
            ValuePad\Core\Amc\Entities\Invoice::class => [
                'amc'
            ],
            ValuePad\Core\Amc\Entities\Item::class => [
                'invoice', 'order'
            ],
            ValuePad\Core\Amc\Entities\Settings::class => [
                'amc', 'id'
            ],
            ValuePad\Core\Appraisal\Entities\Fdic::class => [
                'id'
            ],
            ValuePad\Core\Company\Entities\Fee::class => [
                'company'
            ],
            ValuePad\Core\Shared\Entities\AvailabilityPerCustomer::class => [
                'id', 'user', 'customer'
            ]
        ]
    ],

	'specifications' => [
		ValuePad\Core\Location\Entities\County::class => [
			'zips' => ValuePad\Api\Location\V2_0\Support\CountySpecification::class
		]
	],

    'calculatedProperties' => [
        ValuePad\Core\Document\Entities\Document::class => [
            'url' => ValuePad\Api\Document\V2_0\Support\UrlCalculatedProperty::class,
            'urlEncoded' => ValuePad\Api\Document\V2_0\Support\UrlEncodedCalculatedProperty::class,
        ],
		ValuePad\Core\Appraisal\Entities\Message::class => [
			'isRead' => ValuePad\Api\Appraisal\V2_0\Support\IsMessageReadCalculatedProperty::class
		],
		ValuePad\Core\Customer\Entities\Settings::class => [
			'canAppraiserChangeJobTypeFees' =>
				ValuePad\Api\Appraiser\V2_0\Support\CanAppraiserChangeJobTypeFeesCalculatedProperty::class
		],
        ValuePad\Core\Company\Entities\Company::class => [
            'staff' => ValuePad\Api\Company\V2_0\Transformers\StaffCalculatedProperty::class
        ],
        ValuePad\Core\User\Entities\User::class => [
            'type' => ValuePad\Api\User\V2_0\Transformers\TypeCalculatedProperty::class,
            'isBoss' => ValuePad\Api\User\V2_0\Transformers\IsBossCalculatedProperty::class
        ],
        ValuePad\Core\Appraiser\Entities\Appraiser::class => [
            'availability' => ValuePad\Api\Appraiser\V2_0\Transformers\AvailabilityCalculatedProperty::class,
        ]
    ],

	'modifiers' => [
		ValuePad\Core\Appraisal\Entities\Message::class => [
			'after' => [
				'content' => ['purifier']
			]
		],
		ValuePad\Core\Appraisal\Entities\Order::class => [
			'after' => [
				'instruction' => ['purifier'],
				'comment' => ['purifier'],
				'additionalStatusComment' => ['purifier']
			]
		],
		ValuePad\Core\Appraisal\Objects\Conditions::class => [
			'after' => [
				'explanation' => ['purifier']
			]
		],
		ValuePad\Core\Appraisal\Entities\AcceptedConditions::class => [
			'after' => [
				'additionalComments' => ['purifier']
			]
		],
		ValuePad\Core\Appraisal\Entities\Bid::class => [
			'after' => [
				'comments' => ['purifier']
			]
		],
		ValuePad\Core\Shared\Entities\Availability::class => [
			'after' => [
				'message' => ['purifier']
			]
		],
		ValuePad\Core\Customer\Entities\AdditionalStatus::class => [
			'after' => [
				'comment' => ['purifier']
			]
		],
		ValuePad\Core\Appraisal\Entities\Property::class => [
			'after' => [
				'additionalComments' => ['purifier']
			]
		],
		ValuePad\Core\Appraisal\Entities\Reconsideration::class => [
			'after' => [
				'comment' => ['purifier']
			]
		],
		ValuePad\Core\Appraisal\Objects\Comparable::class => [
			'after' => [
				'comment' => ['purifier']
			]
		],
		ValuePad\Core\Appraisal\Entities\Revision::class => [
			'after' => [
				'message' => ['purifier']
			]
		],
        ValuePad\Core\Appraiser\Entities\Ach::class => [
            'after' => [
                'accountNumber' => ['mask'],
                'routing' => ['mask']
            ]
        ]
	],

    'stringable' => [
        ValuePad\Core\Appraiser\Entities\Appraiser::class => 'appraiser',
		ValuePad\Core\Customer\Entities\Customer::class => 'customer',
		ValuePad\Core\User\Entities\System::class => 'system',
		ValuePad\Core\Back\Entities\Admin::class => 'admin',
		ValuePad\Core\Amc\Entities\Amc::class => 'amc',
		ValuePad\Core\Company\Entities\Manager::class => 'manager'
    ],

	'filter' => ValuePad\Api\Support\Converter\Extractor\FilterWithinContext::class,

	'filters' => [
		ValuePad\Core\Appraiser\Entities\Appraiser::class =>
			ValuePad\Api\Support\Converter\Extractor\Filters\AppraiserFilter::class,

		ValuePad\Core\Appraisal\Entities\Document::class =>
				ValuePad\Api\Support\Converter\Extractor\Filters\AppraisalFilter::class,
		
		ValuePad\Core\Document\Entities\Document::class =>
			ValuePad\Api\Support\Converter\Extractor\Filters\DocumentFilter::class,

		ValuePad\Core\Appraisal\Entities\Order::class =>
			ValuePad\Api\Support\Converter\Extractor\Filters\OrderFilter::class,

		ValuePad\Core\Customer\Entities\Settings::class =>
			ValuePad\Api\Support\Converter\Extractor\Filters\CustomerSettingsFilter::class,

		ValuePad\Core\Customer\Entities\Customer::class =>
			ValuePad\Api\Support\Converter\Extractor\Filters\CustomerFilter::class,

		ValuePad\Core\Company\Entities\Company::class =>
			ValuePad\Api\Support\Converter\Extractor\Filters\CompanyFilter::class,
	]

];

for ($i = 1; $i <= 7; $i ++){
	$config['modifiers'][ValuePad\Core\Appraiser\Entities\EoEx::class]['after']['question'.$i.'Explanation'] = ['purifier'];
}


return $config;