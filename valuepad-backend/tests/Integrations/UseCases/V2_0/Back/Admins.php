<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;

return [
    'createAsAppraiser' => [
        'request' => [
            'url' => 'POST /admins',
            'body' => [
                'username' => 'staffman',
                'password' => 'password',
                'firstName' => 'Staff',
                'lastName' => 'Man',
                'email' => 'staff.man@gmail.com'
            ],
        ],
        'response' => [
            'status' => 403
        ]
    ],
    'validate' => [
        'request' => [
            'url' => 'POST /admins',
            'auth' => 'admin',
            'body' => [
                'username' => ' '
            ],
        ],
        'response' => [
            'errors' => [
                'username' => [
                    'identifier' => 'format',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'password' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'firstName' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'lastName' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'email' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ]
            ]
        ]
    ],
    'create' => [
        'request' => [
            'url' => 'POST /admins',
            'auth' => 'admin',
            'body' => [
                'username' => 'staffman',
                'password' => 'password',
                'firstName' => 'Staff',
                'lastName' => 'Man',
                'email' => 'staff.man@gmail.com'
            ]
        ],
        'response' => [
            'body' => [
                'id' => new Dynamic(Dynamic::INT),
                'username' => 'staffman',
                'firstName' => 'Staff',
                'lastName' => 'Man',
                'email' => 'staff.man@gmail.com',
                'displayName' => 'Staff Man',
                'type' => 'admin'
            ]
        ]
    ],

    'signin:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => 'staffman',
                'password' => 'password'
            ]
        ]
    ],

    'get' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /admins/'.$runtime->getCapture()->get('create.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signin.token')
                ]
            ],
            'response' => [
               'body' => [
                   'id' => new Dynamic(Dynamic::INT),
                   'username' => 'staffman',
                   'firstName' => 'Staff',
                   'lastName' => 'Man',
                   'email' => 'staff.man@gmail.com',
                   'displayName' => 'Staff Man',
                   'type' => 'admin'
               ]
            ]
        ];
    },

    'update' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PATCH /admins/'.$runtime->getCapture()->get('create.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signin.token')
                ],
                'body' => [
                    'username' => 'staffman1',
                    'firstName' => 'Staff1',
                    'lastName' => 'Man1',
                    'email' => 'staff1.man1@gmail.com',
                    'displayName' => 'Staff1 Man1',
                    'type' => 'admin'
                ]
            ]
        ];
    },

    'getUpdated' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /admins/'.$runtime->getCapture()->get('create.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signin.token')
                ]
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'username' => 'staffman1',
                    'firstName' => 'Staff1',
                    'lastName' => 'Man1',
                    'email' => 'staff1.man1@gmail.com',
                    'displayName' => 'Staff1 Man1',
                    'type' => 'admin'
                ]
            ]
        ];
    }
];
