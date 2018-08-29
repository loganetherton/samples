<?php
return [
    'protectors' => [
        'all' => ValuePad\Api\Shared\Protectors\AllProtector::class,
        'auth' => ValuePad\Api\Shared\Protectors\AuthProtector::class,
        'guest' => ValuePad\Api\Shared\Protectors\GuestProtector::class,
        'owner' => ValuePad\Api\Shared\Protectors\OwnerProtector::class,
        'admin' => ValuePad\Api\Shared\Protectors\AdminProtector::class,
    ]
];
