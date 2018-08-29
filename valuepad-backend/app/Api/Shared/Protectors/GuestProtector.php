<?php
namespace ValuePad\Api\Shared\Protectors;

use Ascope\Libraries\Permissions\ProtectorInterface;
use Illuminate\Http\Request;

/**
 *
 *
 */
class GuestProtector implements ProtectorInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function grants()
    {
        return ! $this->request->header('token');
    }
}
