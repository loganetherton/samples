<?php
namespace ValuePad\Api\Shared\Protectors;

use Ascope\Libraries\Permissions\ProtectorInterface;

/**
 *
 *
 */
class AllProtector implements ProtectorInterface
{
    /**
     * @return bool
     */
    public function grants()
    {
        return true;
    }
}
