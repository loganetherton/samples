<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Core\User\Enums\Status;

$amc = uniqid('amc');

return [
    'createAmc:init' => [
        'request' => [
            'url' => 'POST /amcs',
            'auth' => 'guest',
            'body' => [
                'username' => $amc,
                'password' => 'password',
                'email' => 'bestamc@ever.org',
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

    'getSettings' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /amcs/'.$runtime->getCapture()->get('createAmc.id').'/settings',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAmc.token')
                ]
            ],
            'response' => [
                'body' => [
                    'pushUrl' => null
                ]
            ]
        ];
    },
    'updateSettings' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$runtime->getCapture()->get('createAmc.id').'/settings',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAmc.token')
                ],
                'body' => [
                    'pushUrl' => 'http://test.org/push'
                ]
            ]
        ];
    },
    'getSettingsAgain' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /amcs/'.$runtime->getCapture()->get('createAmc.id').'/settings',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAmc.token')
                ]
            ],
            'response' => [
                'body' => [
                    'pushUrl' => 'http://test.org/push'
                ]
            ]
        ];
    },
];
