<?php
use Ascope\QA\Support\Filters\FirstFilter;

return [
    'getAll' => [
        'request' => [
            'url' => 'GET /languages',
        ],
        'response' => [
            'body' => [
                'code' => 'fra',
                'name' => 'French'
            ],
            'filter' => new FirstFilter(function($k, $v){
                return $v['code'] == 'fra';
            }),
            'total' => 22
        ]
    ]
];