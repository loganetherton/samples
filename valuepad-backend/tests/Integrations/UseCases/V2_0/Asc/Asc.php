<?php
use Ascope\QA\Support\Filters\FirstFilter;
use ValuePad\Core\Asc\Enums\Certification;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Response;

return [
    'getAll' => [
        'request' => [
            'url' => 'GET /asc'
        ],
        'response' => [
            'body' => [
                'id' => 5,
                'firstName' => 'first5',
                'lastName' => 'last5',
                'phone' => '(999) 333-1005',
                'address' => '5 Address',
                'city' => 'City 5',
                'zip' => '94205',
                'state' => [
                    'code' => 'CA',
                    'name' => 'California'
                ],
                'certifications' => [Certification::CERTIFIED_RESIDENTIAL],
                'companyName' => 'Company 5',
                'licenseNumber' => 'ABC5XYZ',
                'licenseState' => [
                    'code' => 'TX',
                    'name' => 'Texas'
                ],
                'licenseExpiresAt' => new Dynamic(Dynamic::DATETIME),
				'appraiser' => null,
            ],
            'filter' => new FirstFilter(function($k, $data){
                return $data['id'] == 5;
            }),
            'total' => 10,
        ]
    ],
    'filterByLicenseState' => [
        'request' => [
            'url' => 'GET /asc',
            'parameters' => [
                'filter' => [
                    'licenseState' => 'TX'
                ]
            ]
        ],
        'response' => [
            'assert' => function(Response $response){
				$data = $response->getBody();

				if (!$data){
					return false;
				}

				foreach ($data  as $row){
					if ($row['licenseState']['code'] !== 'TX'){
						return false;
					}
				}

				return true;
			}
        ]
    ],
    'filterByLicenseStateAndLicenseNumber' => [
        'request' => [
            'url' => 'GET /asc',
            'parameters' => [
                'filter' => [
                    'licenseState' => 'TX'
                ],
                'search' => [
                    'licenseNumber' => 'CcCXxX'
                ]
            ]
        ],
        'response' => [
            'total' => 2
        ]
    ]
];