<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Response;
use ValuePad\Tests\Integrations\Support\Filters\MessageAndExtraFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;

$commons = [
	'update.expiresAt' => (new DateTime('+2 year'))->format('c')
];

return [
	'document:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.txt'
			]
		]
	],
	'validate' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/licenses',
				'body' => [
					'number' => 'Wrong',
					'state' => 'CA',
					'expiresAt' => (new DateTime('-1 year'))->format('c'),
					'isFhaApproved' => null,
					'isCommercial' => null,
					'document' => [
						'token' => 'wrong token',
						'id' => $capture->get('document.id')
					],
					'coverage' => [
						[
							'county' => $runtime->getHelper()->county('SACRAMENTO', 'CA'), // belongs to the current state
							'zips' => ['94945'],
						],
						[
							'county' => $runtime->getHelper()->county('KOOCHICHING', 'MN')
						]
					]
				]
			],
			'response' => [
				'errors' => [
					'number' => [
						'identifier' => 'exists',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'expiresAt' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'document' => [
						'identifier' => 'permissions',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'certifications' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'coverage' => [
						'identifier' => 'collection',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => [
							[
								'identifier' => 'dataset',
								'message' => new Dynamic(Dynamic::STRING),
								'extra' => [
									'zips' => [
										'identifier' => 'exists',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									]
								]
							],
							[
								'identifier' => 'dataset',
								'message' => new Dynamic(Dynamic::STRING),
								'extra' => [
									'county' => [
										'identifier' => 'exists',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									]
								]
							]
						]
					]
				]
			]
		];
	},

	'create' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		$expiresAt = (new DateTime('+1 year'))->format('c');

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/licenses',
				'body' => [
					'number' => 'R000000041',
					'state' => 'MN',
					'expiresAt' => $expiresAt,
					'certifications' => ['certified-residential'],
					'isFhaApproved' => true,
					'document' => [
						'token' => $capture->get('document.token'),
						'id' => $capture->get('document.id')
					],
					'coverage' => [
						[
							'county' => $runtime->getHelper()->county('SHERBURNE', 'MN'),
							'zips' => ['55377'],
						],
						[
							'county' => $runtime->getHelper()->county('KOOCHICHING', 'MN')
						]
					]
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'isPrimary' => false,
					'number' => 'R000000041',
					'state' => [
						'code' => 'MN',
						'name' => 'Minnesota'
					],
					'expiresAt' => $expiresAt,
					'certifications' => ['certified-residential'],
					'isFhaApproved' => true,
					'isCommercial' => false,
					'document' => $capture->get('document'),
					'coverage' => [
						[
							'county' => [
								'id' => $runtime->getHelper()->county('SHERBURNE', 'MN'),
								'title' => 'SHERBURNE'
							],
							'zips' => ['55377'],
						],
						[
							'county' => [
								'id' => $runtime->getHelper()->county('KOOCHICHING', 'MN'),
								'title' => 'KOOCHICHING'
							],
							'zips' => []
						]
					]
				]
			],
            'push' => [
                'body' => [
                    [
                        'type' => 'appraiser',
                        'event' => 'create-license',
                        'appraiser' => new Dynamic(Dynamic::INT),
                        'license' =>  new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
		];
	},
	'createWithSameState' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/licenses',
				'body' => [
					'state' => 'MN',
				]
			],
			'response' => [
				'errors' => [
					'state' => [
						'identifier' => 'unique'
					]
				],
				'filter' => new ItemFieldsFilter(['state.identifier'], true)
			]
		];
	},

	'update' => function(Runtime $runtime) use ($commons){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$session->get('user.id').'/licenses/'.$capture->get('create.id'),
				'body' => [
					'expiresAt' => $commons['update.expiresAt'],
					'certifications' => ['certified-general'],
					'isFhaApproved' => false,
					'isCommercial' => true,
					'document' => null,
					'coverage' => []
				]
			],
            'push' => [
                'body' => [
                    [
                        'type' => 'appraiser',
                        'event' => 'update-license',
                        'appraiser' => new Dynamic(Dynamic::INT),
                        'license' =>  new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
		];
	},

	'tryUpdateBooleanWithNull' => function(Runtime $runtime) use ($commons){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$session->get('user.id').'/licenses/'.$capture->get('create.id'),
				'body' => [
					'isFhaApproved' => null,
					'isCommercial' => null,
				]
			],
			'response' => [
				'errors' => [
					'isFhaApproved' => [
						'identifier' => 'not-clearable'
					],
					'isCommercial' => [
						'identifier' => 'not-clearable'
					]
				],
				'filter' => new MessageAndExtraFilter()
			]
		];
	},

	'tryUpdateNumberAndState' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$session->get('user.id').'/licenses/'.$capture->get('create.id'),
				'body' => [
					'number' => 'R000000034',
					'state' => 'TX'
				]
			],

			'response' => [
				'errors' => [
					'number' => [
						'identifier' => 'read-only'
					],
					'state' => [
						'identifier' => 'read-only'
					]
				],
				'filter' => new MessageAndExtraFilter()
			]
		];
	},

	'get' => function(Runtime $runtime) use ($commons){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/licenses/'.$capture->get('create.id'),
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'isPrimary' => false,
					'number' => 'R000000041',
					'state' => [
						'code' => 'MN',
						'name' => 'Minnesota'
					],
					'expiresAt' => $commons['update.expiresAt'],
					'certifications' => ['certified-general'],
					'isFhaApproved' => false,
					'isCommercial' => true,
					'document' => null,
					'coverage' => []
				]
			]
		];

	},
	'getAll' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/licenses'
			],
			'response' => [
				'total' => 2
			]
		];
	},

	'getAllWithPrimary1' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/licenses'
			],
			'response' => [
				'assert' => function(Response $response) use ($session, $capture){
					$data = $response->getBody();

					foreach ($data as $row){
						if ($row['id'] == $capture->get('create.id') && $row['isPrimary'] === true){
							return false;
						}

						if ($row['id'] == $session->get('user.qualifications.primaryLicense.id') && $row['isPrimary'] === false){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'tryChangePrimaryLicense' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/change-primary-license',
				'body' => [
					'license' => 1000,
				]
			],
			'response' => [
				'errors' => [
					'license' => [
						'identifier' => 'permissions',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'changePrimaryLicense' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/change-primary-license',
				'body' => [
					'license' => $capture->get('create.id'),
				]
			]
		];
	},

	'getAllWithPrimary2' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/licenses'
			],
			'response' => [
				'assert' => function(Response $response) use ($session, $capture){
					$data = $response->getBody();

					foreach ($data as $row){
						if ($row['id'] == $capture->get('create.id') && $row['isPrimary'] === false){
							return false;
						}

						if ($row['id'] == $session->get('user.qualifications.primaryLicense.id') && $row['isPrimary'] === true){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'changePrimaryLicenseBack:init' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/change-primary-license',
				'body' => [
					'license' => $session->get('user.qualifications.primaryLicense.id'),
				]
			]
		];
	},


	'delete' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /appraisers/'.$session->get('user.id').'/licenses/'.$capture->get('create.id'),
			],
            'push' => [
                'body' => [
                    [
                        'type' => 'appraiser',
                        'event' => 'delete-license',
                        'appraiser' => new Dynamic(Dynamic::INT),
                        'license' =>  new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
		];
	},

	'tryDeletePrimary' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'DELETE /appraisers/'
					.$session->get('user.id').'/licenses/'
					.$session->get('user.qualifications.primaryLicense.id'),
			],
			'response' => [
				'status' => Response::HTTP_BAD_REQUEST,
				'body' => [
					'code' => Response::HTTP_BAD_REQUEST,
					'message' => 'The primary license cannot be deleted.'
				]
			]
		];
	},

	'getAllAfterDelete' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/licenses'
			],
			'response' => [
				'total' => 1
			]
		];
	},

];
