<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\FirstFilter;

return [

    'validate' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/clients',
                'auth' => 'customer',
                'body' => [
                    'address1' => '12 Market Str.',
                    'address2' => '12/2 Market Str.',
                    'city' => 'San Francisco',
                    'state' => 'CA',
                    'zip' => '94132'
                ]
            ],
            'response' => [
                'errors' => [
                    'name' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'create' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/clients',
                'auth' => 'customer',
                'body' => [
                    'name' => 'XXX Client',
                    'address1' => '12 Market Str.',
                    'address2' => '12/2 Market Str.',
                    'city' => 'San Francisco',
                    'state' => 'CA',
                    'zip' => '94132'
                ]
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'name' => 'XXX Client',
                    'address1' => '12 Market Str.',
                    'address2' => '12/2 Market Str.',
                    'city' => 'San Francisco',
                    'state' => [
                        'code' => 'CA',
                        'name' => 'California'
                    ],
                    'zip' => '94132'
                ]
            ]
        ];
    },

    'get' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /customers/'.$session->get('user.id').'/clients/'.$capture->get('create.id'),
                'auth' => 'customer'
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'name' => 'XXX Client',
                    'address1' => '12 Market Str.',
                    'address2' => '12/2 Market Str.',
                    'city' => 'San Francisco',
                    'state' => [
                        'code' => 'CA',
                        'name' => 'California'
                    ],
                    'zip' => '94132'
                ]
            ]
        ];
    },

    'getAll' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /customers/'.$session->get('user.id').'/clients',
                'auth' => 'customer'
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'name' => 'XXX Client',
                    'address1' => '12 Market Str.',
                    'address2' => '12/2 Market Str.',
                    'city' => 'San Francisco',
                    'state' => [
                        'code' => 'CA',
                        'name' => 'California'
                    ],
                    'zip' => '94132'
                ],
                'filter' => new FirstFilter(function($k, $data) use ($capture){
                    return $data['id'] == $capture->get('create.id');
                })
            ]
        ];
    },

    'update' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id').'/clients/'.$capture->get('create.id'),
                'auth' => 'customer',
                'body' => [
                    'name' => 'ZZZ Client',
                    'address1' => '33 Market Str.',
                    'address2' => '33/3 Market Str.',
                    'city' => 'Los Vegas',
                    'state' => 'NV',
                    'zip' => '90222'
                ]
            ]
        ];
    },

    'getUpdated' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /customers/'.$session->get('user.id').'/clients/'.$capture->get('create.id'),
                'auth' => 'customer'
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'name' => 'ZZZ Client',
                    'address1' => '33 Market Str.',
                    'address2' => '33/3 Market Str.',
                    'city' => 'Los Vegas',
                    'state' => [
                        'code' => 'NV',
                        'name' => 'Nevada'
                    ],
                    'zip' => '90222'
                ]
            ]
        ];
    },

    'updateWithMinimum' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id').'/clients/'.$capture->get('create.id'),
                'auth' => 'customer',
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'address1' => null,
                    'address2' => null,
                    'city' => null,
                    'state' => null,
                    'zip' => null
                ]
            ]
        ];
    },

    'getUpdatedWithMinimum' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /customers/'.$session->get('user.id').'/clients/'.$capture->get('create.id'),
                'auth' => 'customer'
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'name' => 'ZZZ Client',
                    'address1' => null,
                    'address2' => null,
                    'city' => null,
                    'state' => null,
                    'zip' => null
                ]
            ]
        ];
    },
];
