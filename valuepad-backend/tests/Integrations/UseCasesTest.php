<?php
namespace ValuePad\Tests\Integrations;

use Ascope\QA\Support\Asserts\DynamicComparator;
use Ascope\QA\Support\Browser;
use Ascope\QA\Support\Filters\FilterInterface;
use Ascope\QA\Support\RequestFailedException;
use Ascope\QA\Support\Response;
use Ascope\QA\Support\Request;
use ValuePad\Tests\Integrations\Support\Data\Init;
use ValuePad\Tests\Integrations\Support\Runtime\Capture;
use ValuePad\Tests\Integrations\Support\Data\Metadata;
use ValuePad\Tests\Integrations\Support\Runtime\Helper;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Support\Freezer;
use ValuePad\Tests\Integrations\Support\UseCasesIterator;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use RuntimeException;
use Illuminate\Contracts\Console\Kernel;
use ValuePad\Tests\Integrations\Support\Auth\SessionManager;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_Assert;
use SebastianBergmann\Comparator\Factory as Comparator;

/**
 * @author Sergei Melnikov <me@rnr.name>
 */
class UseCasesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    private static $app;

    /**
     * @var Repository
     */
    private $config;

    /**
     * @var SessionManager
     */
    private static $sessions;

    /**
     * @var string
     */
    private $defaultSession;

    /**
     * @var string
     */
    private $upcomingSession;

    /**
     * @var Browser
     */
    private $browser;

    /**
     * @var string
     */
    protected $baseUrl;

	/**
	 * @var array
	 */
	private $resources = ['push', 'live', 'emails', 'mobile'];

	public static function setUpBeforeClass()
	{
		$comparatorFactory = Comparator::getInstance();
		$comparatorFactory->register(new DynamicComparator());
	}

    protected function setUp()
    {
		if (!static::$app){
			static::$app = $this->createApplication();
		}

		$this->config = static::$app->make(Repository::class);
		$this->baseUrl = $this->config->get('qa.integrations.baseUrl');
		$this->defaultSession = $this->config->get('qa.integrations.sessions.default');

        $this->browser = new Browser($this->baseUrl);
    }

	/**
     * @dataProvider provider
     * @param Metadata $metadata
	 * @param Init[] $init
     */
    public function testUseCases(Metadata $metadata, array $init)
    {
        if ($metadata->isAnotherFile()) {
            Capture::reset();
			Freezer::getInstance($metadata->getPreviousPath())->reset();
        }

        $runtime = new Runtime();
        $runtime->setCapture(new Capture());
        $runtime->setSessionManager($this->getSessions());
		$runtime->setConfig($this->config);
		$runtime->setHelper(static::$app->make(Helper::class));

		foreach ($init as $item){
			$this->processInit($item, $runtime);
		}

        $metadata->setRuntime($runtime);

		$todo = $metadata->get('todo');

		if ($todo){
			$this->markTestIncomplete($todo);
		}

		foreach ($this->resources as $name){
			if ($metadata->get($name)){
				$this->clearResource($name);
			}
		}

		$this->verifyResponseMetadata($metadata->get('response', []));

        $response = $this->send($this->buildRequest($metadata->get('request', [])));

		if ($metadata->get('response.debug')){
			echo $response->getContent();
		}

        Capture::add($metadata->getName(), $response->getBody() ?? []);

        $this->assertResponse($metadata->get('response', []), $response);

		foreach ($this->resources as $name){
			if ($resource = $metadata->get($name)){
				$this->assertResource($name, $resource, $runtime);
			}
		}

		$raw = $metadata->get('raw');

		if ($raw){
			$this->callRaw($raw);
		}
    }

	/**
	 * The method prevents putting data for verification outside the "body". This happened many times by accident,
	 * and consequently, the data left unverified since it is allowed to skip "body" in the response.
	 *
	 * IMPORTANT: This verification must never be removed!
	 *
	 * @param array $response
	 */
	private function verifyResponseMetadata(array $response)
	{
		$allowed = ['body', 'status', 'filter', 'debug', 'assert', 'total', 'errors'];

		foreach (array_keys($response) as $item){
			if (!in_array($item, $allowed)){
				throw new RuntimeException('The response metadata contains illegal commands: '. $item);
			}
		}
	}

	/**
	 * @param callable $callback
	 */
	private function callRaw(callable  $callback)
	{
		if (!static::$app->bound(PHPUnit_Framework_TestCase::class)){
			static::$app->bind(PHPUnit_Framework_TestCase::class, function(){
				return $this;
			});
		}

		static::$app->call($callback);
	}

	/**
	 * @param Init $init
	 * @param Runtime $runtime
	 */
	private function processInit(Init $init, Runtime $runtime)
	{
		$init->setRuntime($runtime);

		$request = $init->get('request');

		if ($request !== null){
			$request = $this->buildRequest($request);
			$response = $this->send($request);

			if ($response->getStatusCode() != Response::HTTP_NO_CONTENT
				&& $response->getStatusCode() != Response::HTTP_OK){
				throw new RequestFailedException('Unable to initialize test with "'.$init->getName().'" and :', $request, $response);
			}

			Capture::add(cut_string_right($init->getName(), ':init'), $response->getBody() ?? []);
		}

		$raw = $init->get('raw');

		if ($raw !== null){
			$this->callRaw($raw);
		}
	}

    /**
     * @param array $data
     * @return Request
     */
    private function buildRequest(array $data)
    {
        $auth = array_take($data, 'auth');

        if ($auth) {
            $this->actingAs($auth);
        }

        $call = explode_request($data['url']);

        $request = (new Request($call['method'], $call['url']))
            ->setParameters(array_take($data, 'parameters', []))
            ->setBody(array_take($data, 'body', []))
            ->setFiles(array_take($data, 'files', []))
            ->setOptions(array_take($data, 'options', []))
            ->setIncludes(array_take($data, 'includes', []));

        $headers = array_take($data, 'headers', []);

        foreach ($headers as $name => $value) {
            $request->addHeader($name, $value);
        }

		return $request;
    }

    /**
     * @param array $response
     * @param Response $data
     */
    private function assertResponse(array $response, Response $data)
    {
        $this->assertContent($data, $response);

        $total = array_take($response, 'total');

        if ($total !== null) {

			if (!is_array($total)){
				$total = ['==', $total];
			}

			$o = [
				'>=' => function($a, $b){ return $a >= $b; },
				'<=' => function($a, $b){ return $a <= $b; },
				'==' => function($a, $b){ return $a == $b; },
				'>' => function($a, $b){ return $a > $b; },
				'<' => function($a, $b){ return $a < $b; },
				'<>' => function($a, $b){ return $a != $b; }
			];

            $this->assertTrue($o[$total[0]](count($data->getBody()), $total[1]),
                'The number of retrieved rows doesn\'t match the number of expected rows.');
        }

        if (is_callable($assert = array_take($response, 'assert'))) {
            $this->assertTrue($assert($data));
        }
    }

	/**
	 * @param string $name
	 * @param array|callable $resource
	 * @param Runtime $runtime
	 */
	private function assertResource($name, $resource, Runtime $runtime)
	{
		if (is_callable($resource)){
			$resource = $resource($runtime);
		}

		$data = $this->getResource($name);

       ;

        if (is_callable($assert = array_take($resource, 'assert'))) {
            $this->assertTrue($assert($data));

            return ;
        }

		if (array_take($resource, 'single')){
			$data = array_take($data, 0, []);
		}

		$this->assertResponseBody($data, $resource['body'], array_take($resource, 'filter'));
	}

    /**
     * @param Response $response
     * @param array $options
     */
    private function assertContent(Response $response, array $options)
    {
        $status = array_take($options, 'status');
        $body = array_take($options, 'body');
        $filter = array_take($options, 'filter');
        $errors = array_take($options, 'errors');

        if ($errors) {
            $this->assertResponseStatus($response, Response::UNPROCESSABLE_ENTITY);
            $this->assertResponseBody($response->getBody()['errors'], $errors, $filter);

            return;
        }

        if ($status === Response::HTTP_NO_CONTENT) {
            $this->assertResponseStatus($response, $status);
            $this->assertResponseBlank($response);
            return;
        }

        if ($status === null) {
            if ($response->getStatusCode() === Response::HTTP_NO_CONTENT) {
                $this->assertResponseBlank($response);
                return;
            }

            $status = Response::HTTP_OK;
        }

        $this->assertResponseStatus($response, $status);

        if ($body !== null) {
            $this->assertResponseBody($response->getBody(), $body, $filter);
        }
    }

    /**
     * @param Response $response
     */
    private function assertResponseBlank(Response $response)
    {
        $this->assertTrue($response->isBlank(), 'The response must be blank.');
    }

    /**
     * @return UseCasesIterator
     */
    public function provider()
    {
        return new UseCasesIterator();
    }

    /**
     * @param string $session
     * @return $this
     */
    private function actingAs($session)
    {
        $this->upcomingSession = $session;

        return $this;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws RuntimeException
     */
    private function send(Request $request)
    {
        $token = $this->tryGetToken();

        if ($token) {
            $request->setAuth($token);
        }

        return $this->browser->send($request);
    }

    /**
     * @return string|null
     * @throws RuntimeException
     */
    private function tryGetToken()
    {
        $name = $this->upcomingSession ?: $this->defaultSession;

        $this->upcomingSession = null;

        $session = $this->getSessions()->get($name);

        if ($session->isGuest()) {
            return null;
        }

        $token = $session->getToken();

        return $token;
    }


    /**
     * @return Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * @return SessionManager
     */
    protected function getSessions()
    {
        if (!self::$sessions) {
            self::$sessions = new SessionManager(
                $this->config->get('qa.integrations.sessions.credentials'),
                [
                    'baseUrl' => $this->baseUrl,
                    'endpoint' => '/sessions'
                ]
            );
        }

        return self::$sessions;
    }

	private function clearResource($name)
	{
		$browser = new Browser(static::$app->make('config')->get('app.url'));
		$browser->send(Request::delete('/debug/'.$name));
	}

	/**
	 * @param string $name
	 * @return array
	 */
	private function getResource($name)
	{
		$browser = new Browser(static::$app->make('config')->get('app.url'));
		$response = $browser->send(Request::get('/debug/'.$name));

		return $response->getBody();
	}

	/**
	 * @param Response $response
	 * @param int $expected
	 */
	private function assertResponseStatus(Response $response, $expected)
	{
		$actual = $response->getStatusCode();

		PHPUnit_Framework_Assert::assertEquals($expected, $actual, "Expected status code {$expected}, got {$actual}.");
	}

	/**
	 * @param array $body
	 * @param array $expected
	 * @param FilterInterface $filter
	 * @throws RuntimeException
	 */
	private function assertResponseBody(
		array $body,
		array $expected = [],
		FilterInterface $filter = null
	)
	{
		if ($filter) {
			$body = $filter->filter($body);
		}

		PHPUnit_Framework_Assert::assertEquals($expected, $body);
	}
}