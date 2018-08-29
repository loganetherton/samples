<?php
namespace ValuePad\Mobile\Support;
use Sly\NotificationPusher\Adapter\BaseAdapter;
use Sly\NotificationPusher\Collection\DeviceCollection;
use Sly\NotificationPusher\Model\BaseOptionedModel;
use Sly\NotificationPusher\Model\Device;
use Sly\NotificationPusher\Model\MessageInterface;
use Sly\NotificationPusher\Model\PushInterface;
use RuntimeException;


class ApnsAdapter extends BaseAdapter
{
    const PRODUCTION_URI = 'tls://gateway.push.apple.com:2195';
    const DEVELOPMENT_URI = 'tls://gateway.sandbox.push.apple.com:2195';

    const ERROR_INVALID_TOKEN = 8;

    /**
     * @var callable
     */
    private $errorListener;

    /**
     * Push.
     *
     * @param PushInterface $push
     *
     * @return DeviceCollection
     */
    public function push(PushInterface $push)
    {
        /**
         * @var BaseOptionedModel|MessageInterface $message
         */
        $message = $push->getMessage();

        $options = $message->getOptions();

        $payload = [
            'aps' => [
                'alert' => $message->getText()
            ],
            'payload' => array_get($options, 'data', [])
        ];

        $result = new DeviceCollection();

        $connection = $this->connect();

        /**
         * @var Device $device
         */
        foreach ($push->getDevices() as $device){
            if (fwrite($connection, $this->toJson($payload, $device->getToken(), $message->getText())) === false){
                $this->tryInvokeErrorListener($device, 'Unable to write the provided payload');
                continue ;
            }

            if (!@stream_select($read = [$connection], $write = null, $except = null, 1, 0)) {
                continue ;
            }

            $data = @fread($connection, 6);

            if ($data === false){
                $this->tryInvokeErrorListener($device, 'Unable to read the response');
                continue ;
            }

            if (strlen($data) > 0){
                $response = unpack('Ccommand/Cstatus_code/Nidentifier', $data);

                $code = (int) $response['status_code'];

                if ($code !== 0){
                    $this->tryInvokeErrorListener($device, $this->prepareErrorMessage($code), $code);

                    fclose($connection);
                    $connection = $this->connect();

                    continue ;
                }
            }

            $result->add($device);
        }

        fclose($connection);

        return $result;
    }

    private function prepareErrorMessage($code)
    {
        switch ($code){
            case 1:
                return 'Processing error';
            case 2:
                return 'Missing device token';
            case 3:
                return 'Missing topic';
            case 4:
                return 'Missing payload';
            case 5:
                return 'Invalid token size';
            case 6:
                return 'Invalid topic size';
            case 7:
                return 'Invalid payload size';
            case 8:
                return 'Invalid token';
            case 10:
                return 'Shutdown';
            case 128:
                return 'Protocol error (APNs could not parse the notification)';
            default:
                return 'Unknown error';
        }
    }

    /**
     * @param Device $device
     * @param string $error
     * @param int $code
     */
    private function tryInvokeErrorListener(Device $device, $error, $code = null)
    {
        if ($this->errorListener){
            call_user_func($this->errorListener, $device, $error, $code);
        }
    }

    /**
     * @param array $data
     * @param string $token
     * @param string $message
     * @return string
     */
    private function toJson(array $data, $token, $message)
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        return pack('CNNnH*', 1, sha1($token.$message), null, 32, $token).pack('n', strlen($data)).$data;
    }

    private function connect()
    {
        $errorCode = $errorMessage = null;

        $certificate = $this->getParameter('certificate');

        if (!$certificate === null){
            throw new RuntimeException('Certificate has not been provided');
        }

        $resource = stream_socket_client(
            $this->getUri(),
            $errorCode,
            $errorMessage,
            ini_get('default_socket_timeout'),
            STREAM_CLIENT_CONNECT,
            stream_context_create([
                'ssl' => [
                    'local_cert' => $certificate,
                ]
            ])
        );

        if ($resource === false){
            throw new RuntimeException($errorMessage, $errorCode);
        }

        stream_set_blocking($resource, 0);
        stream_set_write_buffer($resource, 0);

        return $resource;
    }

    /**
     * @return string
     */
    private function getUri()
    {
        if ($this->isProductionEnvironment()){
            return self::PRODUCTION_URI;
        }

        return self::DEVELOPMENT_URI;
    }

    /**
     * Supports.
     *
     * @param string $token
     *
     * @return bool
     */
    public function supports($token)
    {
        return (ctype_xdigit($token) && 64 == strlen($token));
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
        return ['certificate'];
    }

    /**
     * @param callable $callback
     */
    public function setErrorListener(callable  $callback)
    {
        $this->errorListener = $callback;
    }
}
