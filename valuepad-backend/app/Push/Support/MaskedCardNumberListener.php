<?php
namespace ValuePad\Push\Support;

use GuzzleHttp\Psr7\Request;
use ValuePad\Core\User\Entities\User;

class MaskedCardNumberListener
{
    /**
     * @var Listener
     */
    private $listener;

    /**
     * @param Listener $listener
     */
    public function __construct(Listener $listener)
    {
        $this->listener = $listener;
    }

    /**
     * @param Request $request
     * @param Response|Exception $responseOrException
     * @param array $data
     * @param User $target
     */
    public function __invoke(Request $request, $responseOrException, array $data, User $target)
    {
        $body = json_decode($request->getBody());

        if (isset($body['creditCard']['number'])) {
            $body['creditCard']['number'] = 'XXXX'.mb_substr($body['creditCard']['number'], -4);
            $request = new Request('POST', $request->getUri(), $request->getHeaders(), json_encode($body));
        }

        $this->listener($request, $responseOrException, $data, $target);
    }
}