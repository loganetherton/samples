<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\User\Enums\Status;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;

$appraiser = uniqid('appraiser');
$amc = uniqid('amc');

return [
    'createW9:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.pdf'
            ]
        ]
    ],

    'createEoDocument:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.pdf'
            ]
        ]
    ],

    'createAppraiser:init' => function(Runtime $runtime) use ($appraiser){

        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $appraiser,
            'password' => 'password',
            'w9' => [
                'id' => $capture->get('createW9.id'),
                'token' => $capture->get('createW9.token')
            ],
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'CA'
                ],
            ],
            'eo' => [
                'document' => [
                    'id' => $capture->get('createEoDocument.id'),
                    'token' => $capture->get('createEoDocument.token')
                ]
            ]
        ]);

        $data['email'] = $appraiser.'@test.org';

        return [
            'request' => [
                'url' => 'POST /appraisers',
                'body' => $data
            ]
        ];
    },

    'createAmc:init' => [
        'request' => [
            'url' => 'POST /amcs',
            'auth' => 'guest',
            'body' => [
                'username' => $amc,
                'password' => 'password',
                'email' => $amc.'@test.org',
                'companyName' => 'Best AMC Ever!',
                'address1' => '123 Wall Str.',
                'address2' => '124B Wall Str.',
                'city' => 'New York',
                'zip' => '44211',
                'state' => 'NY',
                'lenders' => 'VMX, TTT, abc',
                'phone' => '(423) 553-1211',
                'fax' => '(423) 553-1212'
            ]
        ],
    ],

    'approveAmc:init' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$runtime->getCapture()->get('createAmc.id'),
                'auth' => 'admin',
                'body' => [
                    'status' => Status::APPROVED
                ]
            ]
        ];
    },

    'signinAmc:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $amc,
                'password' => 'password'
            ]
        ]
    ],

    'validate' => function() use ($appraiser){
        return [
            'request' => [
                'url' => 'POST /help/hints',
                'body' => [
                    'email' => 'no_email_here@email.com'
                ]
            ],
            'response' => [
                'errors' => [
                    'email' => [
                        'identifier' => 'not-found',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'hints' => function() use ($appraiser){
        return [
            'request' => [
                'url' => 'POST /help/hints',
                'body' => [
                    'email' => $appraiser.'@test.org'
                ]
            ],
            'emails' => [
                'body' => [
                    [
                        'from' => [
                            'no-reply@valuepad.com' => 'The ValuePad Team'
                        ],
                        'to' => [
                            $appraiser.'@test.org' => 'Jack Black'
                        ],
                        'subject' => 'Trouble Sign-In',
                        'contents' => new Dynamic(function($value) use ($appraiser){
                            return str_contains($value, $appraiser);
                        })
                    ]
                ]
            ]
        ];
    },

    'updateAmc:init' => function(Runtime $runtime) use ($appraiser){
        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$runtime->getCapture()->get('createAmc.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAmc.token')
                ],
                'body' => [
                    'email' => $appraiser.'@test.org'
                ]
            ]
        ];
    },

    'hintsWithMultipleUsernames' => function() use ($appraiser, $amc){
        return [
            'request' => [
                'url' => 'POST /help/hints',
                'body' => [
                    'email' => $appraiser.'@test.org'
                ]
            ],
            'emails' => [
                'body' => [
                    [
                        'from' => [
                            'no-reply@valuepad.com' => 'The ValuePad Team'
                        ],
                        'to' => [
                            $appraiser.'@test.org' => ''
                        ],
                        'subject' => 'Trouble Sign-In',
                        'contents' => new Dynamic(function($value) use ($appraiser, $amc){
                            return str_contains($value, [$appraiser, $amc]);
                        })
                    ]
                ]
            ]
        ];
    }
];
