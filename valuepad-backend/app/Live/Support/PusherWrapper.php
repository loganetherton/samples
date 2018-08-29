<?php
namespace ValuePad\Live\Support;

use Pusher;

class PusherWrapper implements PusherWrapperInterface
{
	/**
	 * @var Pusher
	 */
	private $pusher;

	/**
	 * @param string $key
	 * @param string $secret
	 * @param int $id
	 * @param array $options
	 */
	public function __construct($key, $secret, $id, array $options = [])
	{
		$this->pusher = new Pusher($key, $secret, $id, $options);
	}

	/**
	 * @param array $channels
	 * @param $event
	 * @param array $data
	 * @return bool
	 */
	public function trigger(array $channels, $event, array $data)
	{
		foreach (array_chunk($channels, 10) as $chunk){
			$this->pusher->trigger($chunk, $event, $data);
		}
	}

	/**
	 * @param string $channel
	 * @param string $socket
	 * @return string
	 */
	public function auth($channel, $socket)
	{
		return $this->pusher->socket_auth($channel, $socket);
	}
}
