<?php
namespace ValuePad\Seeding;

use Ascope\QA\Support\Browser;
use Ascope\QA\Support\DefaultFakerFactory;
use Ascope\QA\Support\FakerFactoryInterface;
use Ascope\QA\Support\Request;
use Ascope\QA\Support\RequestFailedException;
use Ascope\QA\Support\Response;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Container\Container;
use RuntimeException;
use ValuePad\Core\Asc\Entities\AscAppraiser;
use ValuePad\Core\Asc\Enums\Certifications;
use ValuePad\Core\Location\Entities\State;
use DateTime;
use ValuePad\Core\Asc\Enums\Certification;
use ValuePad\Support\Shortcut;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;

abstract class AbstractSeeder implements SeederInterface
{
	/**
	 * @var Browser
	 */
	protected $browser;

	/**
	 * @var FakerFactoryInterface
	 */
	protected $faker;

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * @var EntityManagerInterface
	 */
	protected $entityManager;

	/**
	 * @var array
	 */
	private $sessions = [];

	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->browser = new Browser($container->make('config')->get('app.url')
			.'/'.Shortcut::prependGlobalRoutePrefix('v2.0'));
		$this->faker = (new DefaultFakerFactory())->create();
		$this->container = $container;
		$this->entityManager = $container->make(EntityManagerInterface::class);
	}

	/**
	 * @param Request $request
	 * @param string $error
	 * @param int $status
	 * @return Response
	 */
	protected function send(Request $request, $error, $status = Response::HTTP_OK)
	{
		$response = $this->browser->send($request);

		if ($response->getStatusCode() != $status){
			throw new RequestFailedException(
				$error,
				$request,
				$response
			);
		};

		return $response;
	}

	/**
	 * @param array $data
	 */
	protected function registerAppraiser(array $data)
	{
		$eoDocument = $this->createDocument();
		$w9 = $this->createDocument();

		$data['eo']['document'] = [
			'id' => $eoDocument['id'],
			'token' => $eoDocument['token']
		];

		$data['w9'] = [
			'id' => $w9['id'],
			'token' => $w9['token']
		];

		$request = Request::post('/appraisers', AppraisersFixture::get($data));

		$message = 'Unable to register the appraiser with the "'
			.$data['username'].'" username and the "'.$data['password'].'" password.';

		$this->send($request, $message);
	}

	/**
	 * @param array $data
	 * @return int
	 */
	protected function registerCustomer(array $data)
	{
		$request = Request::post('/customers', $data);

		$message = 'Unable to register the customer with the "'
			.$data['username'].'" username and the "'.$data['password'].'" password.';

		$response = $this->send($request, $message);

		$id = $response->getBody()['id'];

		$request = Request::patch('/customers/'.$id.'/settings', [
			'pushUrl' => $this->container->make('config')->get('app.url').'/debug/push'
		]);

		$token = $this->login($data['username'], $data['password'])['token'];

		$request->addHeader('Token', $token);

		$this->send(
			$request,
			'Unable to set "pushUrl" to the customer with the "'.$id.'" ID.',
			Response::HTTP_NO_CONTENT
		);

		return $id;
	}

	/**
	 * @return array
	 * @throws RuntimeException
	 */
	protected function createDocument()
	{
		$request = Request::post('/documents');
		$request->setFiles([
			'document' => __DIR__.'/test.txt'
		]);

		return $this
			->send($request, 'Unable to create document.')
			->getBody();
	}

	/**
	 * @param int $i
	 * @param array $data
	 * @return AscAppraiser
	 */
	protected function createAscAppraiser($i, array $data = [])
	{
		$appraiser = new AscAppraiser();

		$appraiser->setFirstName('first'.$i);
		$appraiser->setLastName('last'.$i);
		$appraiser->setPhone('(999) 333-'.(1000+$i));

		$appraiser->setAddress($i.' Address');
		$appraiser->setCity('City '.$i);

		$code = array_take($data, 'state', $this->faker->randomElement(['CA', 'FL', 'NY']));

		/**
		 * @var State $state
		 */
		$state = $this->entityManager
			->getReference(State::class, $code);

		$appraiser->setState($state);

		$appraiser->setZip((string)(94200 + $i));

		$certifications = array_take($data, 'certifications', [$this->faker->randomElement(Certification::toArray())]);

		$appraiser->setCertifications(Certifications::make($certifications));
		$appraiser->setCompanyName('Company '.$i);
		$appraiser->setLicenseExpiresAt(new DateTime('+'.$this->faker->randomDigitNotNull.' years'));

		$code = array_take($data, 'licenseState', $this->faker->randomElement(['CA', 'FL', 'NY']));

		/**
		 * @var State $state
		 */
		$state = $this->entityManager
			->getReference(State::class, $code);


		$appraiser->setLicenseState($state);

		$licenseNumber = array_take($data, 'licenseNumber', 'ABC'.$i.'XYZ');

		$appraiser->setLicenseNumber($licenseNumber);

		return $appraiser;
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @return array
	 */
	protected function login($username, $password)
	{
		if (!isset($this->sessions[$username])){
			$request = Request::post('/sessions', [
				'username' => $username,
				'password' => $password
			]);

			$response = $this->send($request, 'Unable to login with: '.$username.' and '.$password);

			$this->sessions[$username] = $response->getBody();
		}

		return $this->sessions[$username];
	}

	/**
	 * @param array $customer
	 * @param array $appraiser
	 */
	protected function processInvitation(array $customer, array $appraiser)
	{
		$data = $this->login($customer['username'], $customer['password']);


		$request = Request::post('/customers/'.$data['user']['id'].'/invitations', [
			'ascAppraiser' => $appraiser['ascAppraiser']
		]);

		$request->setAuth($data['token']);

		$response = $this->send($request, 'Unable to create an invitation.');
		$invitationId = $response->getBody()['id'];

		$data = $this->login($appraiser['username'], $appraiser['password']);

		$request = Request::post('/appraisers/'.$data['user']['id'].'/invitations/'.$invitationId.'/accept');

		$request->setAuth($data['token']);

		$this->send($request, 'Unable to accept the invitation with: '.$invitationId, Response::HTTP_NO_CONTENT);
	}
}
