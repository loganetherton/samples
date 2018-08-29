<?php
namespace ValuePad\Push\Support;

use Log;
use Exception;
use ValuePad\Support\Chance\Attempt;
use ValuePad\Support\Chance\LogicHandlerInterface;
use ValuePad\Support\Chance\Coordinator;

class Processor implements LogicHandlerInterface
{
    const TAG = 'push';

    /**
     * @var Coordinator
     */
    private $coordinator;

    /**
     * @var Listener
     */
    private $listener;

    /**
     * @param Coordinator $coordinator
     * @param Listener $listener
     */
    public function __construct(Coordinator $coordinator, Listener $listener)
    {
        $this->coordinator = $coordinator;
        $this->listener = $listener;
    }

    /**
     * @param Payload $payload
     * @param callable $onError
     */
	public function process(Payload $payload, callable $onError = null)
	{
        $calls = array_map(function(Call $call){
            return [
                'url' => $call->getUrl(),
                'secret1' => $call->getSecret1(),
                'secret2' => $call->getSecret2(),
                'target' => $call->getUser(),
            ];
        }, $payload->getCalls());

        $data = $payload->getData();

        foreach ($calls as $call){
            try {
                $this->run($call, $data);
            } catch (Exception $ex) {

                $attempt = new Attempt();
                $attempt->setTag(static::TAG);
                $attempt->setData([
                    'data' => $data,
                    'call' => $call
                ]);

                $this->coordinator->schedule($attempt);

                if ($onError){
                    $onError($call, $data);
                }
            }
        }
    }

    /**
     * @param Attempt $attempt
     * @return bool
     */
    public function handle(Attempt $attempt)
    {
       try {
           $this->run(
               $attempt->getData()['call'],
               $attempt->getData()['data']
           );
       } catch (Exception $ex) {
           return false;
       }

       return true;
    }

    /**
     * @param array $call
     * @param array $data
     */
    private function run(array $call, array $data)
    {
        $tunnel = new Tunnel($call['url'], $call['secret1'], $call['secret2'], $call['target']);
        $tunnel->setListener($this->listener);

        $tunnel->push($data['type'], $data['event'], $data);
    }

    /**
     * @param Attempt $attempt
     */
    public function outOfAttempts(Attempt $attempt)
    {
        $call = $attempt->getData()['call'];
        $data = $attempt->getData()['data'];

        $url = $call['url'];
        $event = $data['type'].':'.$data['event'];


        Log::warning('Unable to reach customer via "'.$url.'" on "'.$event.'" event.');
    }
}
