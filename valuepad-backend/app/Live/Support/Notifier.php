<?php
namespace ValuePad\Live\Support;

use Illuminate\Contracts\Container\Container;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\Shared\Interfaces\NotifierInterface;

class Notifier implements NotifierInterface
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var EnvironmentInterface
	 */
	private $environment;

    /**
     * @var PusherWrapperInterface
     */
    private $pusher;

	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->environment = $container->make(EnvironmentInterface::class);
        $this->pusher = $container->make(PusherWrapperInterface::class);
	}

	/**
	 * @param object $notification
	 */
	public function notify($notification)
	{
		/**
		 * We don't want to notify pusher about events triggered by Appraisal Scope when importing data
		 */
		if ($this->environment->isRelaxed()){
			return ;
		}

		$handlers = $this->container->make('config')->get('alert.live.handlers', []);

		$class = get_class($notification);

		if (!isset($handlers[$class])){
			return ;
		}

		/**
		 * @var HandlerInterface $handler
		 */
		$handler = $this->container->make($handlers[$class]);

		$event = $handler->handle($notification);

		if ($event === null || count($event->getChannels()) == 0){
			return ;
		}

        $channels = array_map(function(Channel $channel){ return (string) $channel; }, $event->getChannels());
        $this->pusher->trigger($channels, (string) $event, $this->prepareData($event));
	}

    /**
     * @param Event $event
     * @return array
     */
    private function prepareData(Event $event)
    {
        $data = $event->getData();

        if (!in_array((string) $event, [
            'order:create-document',
            'order:update-document',
            'order:delete-document'
        ]) || $event->getData()['document']['showToAppraiser'] === true){
            return $data;
        }

        $data['document'] = [
            'id' => $data['document']['id'],
            'showToAppraiser' => $data['document']['showToAppraiser'],
            'extra' => array_map(function($row){ return [
                'id' => $row['id'],
                'format' => $row['format']
            ]; }, $data['document']['extra'])
        ];

        return $data;
    }
}
