<?php

return [
    'integrations' => [
        'baseUrl' => env('BASE_URL').'/v2.0',
        'sessions' => [
            'default' => 'appraiser',
            'credentials' => [
                'appraiser' => [
                    'username' => 'appraiser',
                    'password' => 'password'
                ],
				'customer' => [
					'username' => 'customer',
					'password' => 'password'
				],
				'admin' => [
					'username' => 'superadmin',
					'password' => 'password'
				],
				'amc' => [
					'username' => 'testamc',
					'password' => 'password'
				],
                'guest' => null
            ]
        ],
    ]
];