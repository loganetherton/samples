<?php
namespace ValuePad\Tests\Integrations\Support\Auth;

use Ascope\QA\Support\Browser;
use Ascope\QA\Support\Request;
use Ascope\QA\Support\Response;
use RuntimeException;

/**
 * @author Sergei Melnikov <me@rnr.name>
 */
class SessionManager
{
    /**
     * @var Session[]
     */
    private $sessions = [];

    /**
     * @var Browser
     */
    private $browser;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @param array $sessions
     * @param array $options
     */
    public function __construct(array $sessions, array $options)
    {
        foreach ($sessions as $name => $config){
            $this->sessions[$name] = new Session($name, $config);
        }

        $this->browser = new Browser($options['baseUrl']);
        $this->endpoint = $options['endpoint'];
    }

    /**
     * @param string $name
     * @return Session
     */
    public function get($name)
    {
        $session = $this->sessions[$name];

        if ($session->isGuest() || $session->isInitialized()){
            return $session;
        }

        $username = $session->getConfig('username');
        $password = $session->getConfig('password');

        $request = Request::post($this->endpoint, [
            'username' => $username,
            'password' => $password
        ]);

		$request->addHeader('Include', 'user.company,user.qualifications,user.phone');

        $response = $this->browser->send($request);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new RuntimeException(
                "Unsuccessful attempt to login with username '$username' and password '$password'"
            );
        }

        $data = $response->getBody();

        $token = $data['token'];

        $session->setToken($token);
        $session->setData($data);
        $session->setInitialized(true);

        return $session;
    }

    /**
     * @param string $session
     * @return bool
     */
    public function has($session)
    {
        return array_key_exists($session, $this->sessions);
    }

    /**
     * @param $session
     * @throws RuntimeException
     */
    public function check($session)
    {
        if (!$this->has($session)) {
            throw new RuntimeException("Session '{$session}' doesn't exist'");
        }
    }
}