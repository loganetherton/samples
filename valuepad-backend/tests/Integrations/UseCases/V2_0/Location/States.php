<?php
use Ascope\QA\Support\Filters\FirstFilter;

return [
    'getAll' => [
        'request' => [
            'url' => 'GET /location/states',
        ],
        'response' => [
            'body' => [
                'code' => 'CA',
                'name' => 'California'
            ],
            'filter' => new FirstFilter(function($k, $v){
                return $v['code'] == 'CA';
            }),
            'total' => 62
        ]
    ]
];