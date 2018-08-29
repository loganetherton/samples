<?php
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\Document\Enums\Format;
use Ascope\QA\Support\Response;

return [
	'validate' => [
		'request' => [

			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.sh'
			],
		],
		'response' => [
			'errors' => [
				'document' => [
					'identifier' => 'format',
					'message' => new Dynamic(Dynamic::STRING),
					'extra' => []
				]
			]
		]
	],
    'create' => [
        'request' => [

            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.txt'
            ],
        ],
        'response' => [
            'body' => [
                'id' => new Dynamic(Dynamic::INT),
                'token' => new Dynamic(Dynamic::STRING),
                'name' => 'test.txt',
                'url' => new Dynamic(Dynamic::STRING),
                'urlEncoded' => new Dynamic(Dynamic::STRING),
                'size' => new Dynamic(Dynamic::INT),
                'format' => Format::TXT,
                'uploadedAt' => new Dynamic(Dynamic::DATETIME)
            ]
        ]
    ],
	'permissionsExternal' => [
		'request' => [
			'url' => 'POST /documents/external',
			'body' => [
				'name' => 'my-file.xml',
				'size' => 42114124,
				'format' => Format::XML,
				'url' => 'https://www.dropbox.com/my-file.xml'
			]
		],
		'response' => [
			'status' => Response::FORBIDDEN
		]
	],

	'validateExternal' => [
		'request' => [
			'url' => 'POST /documents/external',
			'headers' => [
				'System-Identifier' => 'muw0t5dFsRsQIMsJoiBr3vTlMunW1d8Z'
			],
			'body' => [
				'name' => ' ',
				'size' => 0,
				'url' => ' '
			]
		],
		'response' => [
			'errors' => [
				'name' => [
					'identifier' => 'empty',
					'message' => new Dynamic(Dynamic::STRING),
					'extra' => []
				],
				'size' => [
					'identifier' => 'greater',
					'message' => new Dynamic(Dynamic::STRING),
					'extra' => []
				],
				'url' => [
					'identifier' => 'empty',
					'message' => new Dynamic(Dynamic::STRING),
					'extra' => []
				],
				'format' => [
					'identifier' => 'required',
					'message' => new Dynamic(Dynamic::STRING),
					'extra' => []
				],
			]
		]
	],

	'createExternal' => [
		'request' => [

			'url' => 'POST /documents/external',
			'headers' => [
				'System-Identifier' => 'muw0t5dFsRsQIMsJoiBr3vTlMunW1d8Z'
			],
			'body' => [
				'name' => 'my-file.xml',
				'size' => 42114124,
				'format' => Format::XML,
				'url' => 'https://www.dropbox.com/my-file.xml'
			]
		],
		'response' => [
			'body' => [
				'id' => new Dynamic(Dynamic::INT),
				'token' => new Dynamic(Dynamic::STRING),
				'name' => 'my-file.xml',
				'size' => 42114124,
				'format' => Format::XML,
				'url' => 'https://www.dropbox.com/my-file.xml',
				'urlEncoded' => 'https://www.dropbox.com/my-file.xml',
				'uploadedAt' => new Dynamic(Dynamic::DATETIME)
			]
		]
	]
];
