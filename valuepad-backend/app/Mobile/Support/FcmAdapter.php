<?php
namespace ValuePad\Mobile\Support;
use GuzzleHttp\Client;
use Sly\NotificationPusher\Adapter\BaseAdapter;
use Sly\NotificationPusher\Collection\DeviceCollection;
use Sly\NotificationPusher\Model\BaseOptionedModel;
use Sly\NotificationPusher\Model\DeviceInterface;
use Sly\NotificationPusher\Model\MessageInterface;
use Sly\NotificationPusher\Model\PushInterface;

class FcmAdapter extends BaseAdapter
{
    const URL = 'https://fcm.googleapis.com/fcm/send';

    /**
     * @var Client
     */
    private $client;

    public function __construct(array $parameters)
    {
        parent::__construct($parameters);

        $this->client = new Client();
    }

    /**
     * Push.
     *
     * @param PushInterface $push Push
     *
     * @return DeviceCollection
     */
    public function push(PushInterface $push)
    {
        /**
         * @var BaseOptionedModel|MessageInterface $message
         */
        $message = $push->getMessage();

        $result = new DeviceCollection();

        $notification = array_merge($message->getOption('notification', []), ['text' => $message->getText()]);

        $payload = $message->getOption('data', []);

        $payload['notification'] = array_merge($payload['notification'] ?? [], $notification);


        foreach ($push->getDevices()->getTokens() as $token){

            $response = $this->client->post(self::URL, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'key='.$this->getParameter('key')
                ],
                'body' => json_encode([
                    'data' => [
                        'payload' => $payload
                    ],
                    'registration_ids' => [$token]
                ])
            ]);

            if ((int) $response->getStatusCode() === 200){

                /**
                 * @var DeviceInterface $device
                 */
                $device = $push->getDevices()->get($token);

                $result->add($device);
            }
        }

        return $result;
    }

    /**
     * Supports.
     *
     * @param string $token Token
     *
     * @return boolean
     */
    public function supports($token)
    {
        return is_string($token) && $token != '';
    }

    /**
     * Get defined parameters.
     *
     * @return array
     */
    public function getDefinedParameters()
    {
        return [];
    }

    /**
     * Get default parameters.
     *
     * @return array
     */
    public function getDefaultParameters()
    {
        return [];
    }

    /**
     * Get required parameters.
     *
     * @return array
     */
    public function getRequiredParameters()
    {
        return ['key'];
    }
}
