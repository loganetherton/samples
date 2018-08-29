<?php
namespace ValuePad\Mobile\Support;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Serializable;
use ValuePad\Core\User\Entities\User;
use Sly\NotificationPusher\Collection\DeviceCollection;
use Sly\NotificationPusher\Model\Device;
use Sly\NotificationPusher\Model\Message;
use Sly\NotificationPusher\Model\Push;
use Sly\NotificationPusher\PushManager;
use ValuePad\Core\User\Enums\Platform;
use ValuePad\Core\User\Services\DeviceService;
use Exception;
use Log;
use Illuminate\Config\Repository as Config;

class Job implements ShouldQueue, Serializable, SelfHandling
{
	/**
	 * @var array
	 */
	private $users = [];

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @var array
	 */
	private $extra;

	/**
	 * @var array
	 */
	private $notification;

	/**
	 * @param Tuple $tuple
	 */
	public function __construct(Tuple $tuple)
	{
		$this->users = array_map(function(User $user){ return $user->getId(); }, $tuple->getUsers());

		$news = $tuple->getNews();

		$this->message = $news->getMessage();
		$this->extra = $news->getExtra();

		$this->notification = [
			'category' => $news->getCategory(),
			'name' => $news->getName()
		];
	}

	/**
	 * @param DeviceService $deviceService
	 * @param Config $config
     * @param AdapterFactory $adapterFactory
	 */
	public function handle(DeviceService $deviceService, Config $config, AdapterFactory $adapterFactory)
	{
		$ios = new DeviceCollection();
		$android = new DeviceCollection();

		foreach ($deviceService->getAllByUserIds($this->users) as $device){
			if ($device->getPlatform()->is(Platform::IOS)){
				$ios->add(new Device($device->getToken()));
			}

			if ($device->getPlatform()->is(Platform::ANDROID)){
				$android->add(new Device($device->getToken()));
			}
		}

		$manager = new PushManager($config->get('app.push_notifications.environment', PushManager::ENVIRONMENT_DEV));

		try {

			$manager->add(new Push(
				$adapterFactory->ios(),
				$ios,
				new Message($this->message, [
					'data' => [
						'extra' => $this->extra,
						'notification' => $this->notification
					]
				])
			));

			$manager->add(new Push(
				$adapterFactory->android(),
				$android,
				new Message($this->message, [
				    'notification' => [
                        'title' => 'ValuePad Notification',
                        'click_action' => 'order'
                    ],
					'data' => [
						'extra' => $this->extra,
						'notification' => $this->notification
					]
				])
			));

			$manager->push();

		} catch (Exception $ex){
			Log::warning($ex);
		}
	}

	/**
	 * @return string
	 */
	public function serialize()
	{
		return json_encode([
			'users' => $this->users,
			'message' => $this->message,
			'extra' => $this->extra,
			'notification' => $this->notification
		]);
	}

	/**
	 * @param string $serialized
	 */
	public function unserialize($serialized)
	{
		$data = json_decode($serialized, true);
		$this->users = $data['users'];
		$this->message = $data['message'];
		$this->extra = $data['extra'];
		$this->notification = $data['notification'];
	}
}
