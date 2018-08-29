<?php
namespace ValuePad\Seeding;

use Ascope\QA\Support\Request;
use Ascope\QA\Support\Response;
use DateTime;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use ValuePad\Core\Asc\Enums\Certification;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Entities\JobType;
use ValuePad\Core\Customer\Enums\Criticality;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\JobType\Entities\JobType as Local;
use ValuePad\Core\User\Enums\Status;

class TestsSeeder extends AbstractSeeder
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Initializer constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
		parent::__construct($container);
        $this->config = $container->make('config');
    }

    public function seed()
    {
		$this->createAscAppraisers();

		$appraiserCredentials = $this->config->get('qa.integrations.sessions.credentials.appraiser');

		$this->registerAppraiser([
			'username' => $appraiserCredentials['username'],
			'password' => $appraiserCredentials['password'],
			'qualifications' => [
				'primaryLicense' => [
					'number' => 'CCCXXX4',
					'state' => 'TX',
				]
			]
		]);

		$session = $this->login($appraiserCredentials['username'], $appraiserCredentials['password']);

		$request = Request::put('/appraisers/'.$session['user']['id'].'/payment/credit-card', [
			'number' => '1111111111111116',
			'expiresAt' => [
				'month' => 12,
				'year' => (int) (new DateTime('+10 years'))->format('Y')
			],
			'code' => '222'
		]);

		$request->setAuth($session['token']);

		$this->send($request, 'Unable to create credit card for the "'.$session['user']['id'].'" appraiser.');

		$customerCredentials = $this->config->get('qa.integrations.sessions.credentials.customer');

		$customerId = $this->registerCustomer([
			'username' => $customerCredentials['username'],
			'password' => $customerCredentials['password'],
			'name' => 'ascope'
		]);

		$request = Request::patch('/customers/'.$customerId.'/settings', [
			'preventViolationOfDateRestrictions' => Criticality::HARDSTOP
		]);

		$session = $this->login($customerCredentials['username'], $customerCredentials['password']);

		$request->setAuth($session['token']);

		$this->send($request, 'Unable to update the "preventViolationOfDateRestrictions" setting.', Response::HTTP_NO_CONTENT);

		$this->processInvitation(
			[
				'username' => $customerCredentials['username'],
				'password' => $customerCredentials['password'],
			],
			[
				'username' => $appraiserCredentials['username'],
				'password' => $appraiserCredentials['password'],
				'ascAppraiser' => 4,
			]
		);

		$this->createJobTypes($customerId);

		$this->createClients($customerId, $session['token']);

        /**
         * @var CustomerService $customerService
         */
        $customerService = $this->container->make(CustomerService::class);

        $customerService->relateWithAmc($customerId, $this->registerAmc());
    }

	private function createAscAppraisers()
	{
		for ($i = 1; $i <= 50; $i++){

			if ($i == 5){
				$appraiser = $this->createAscAppraiser($i, [
					'state' => 'CA',
					'licenseState' => 'TX',
					'certifications' => [Certification::CERTIFIED_RESIDENTIAL]
				]);
			} elseif(in_array($i, [4, 10])){
				$appraiser = $this->createAscAppraiser($i, [
					'licenseState' => 'TX',
					'licenseNumber' => 'CCCXXX'.$i
				]);
			} elseif (in_array($i, [41, 34])){
				$appraiser = $this->createAscAppraiser($i, [
					'licenseState' => 'MN',
					'licenseNumber' => 'R0000000'.$i
				]);
			} else {
				$appraiser = $this->createAscAppraiser($i);
			}

			$this->entityManager->persist($appraiser);
		}

		$this->entityManager->flush();
	}

	private function registerAmc()
	{
		$credentials = $this->config->get('qa.integrations.sessions.credentials.amc');

		$request = Request::post('/amcs', [
			'username' => $credentials['username'],
			'password' => $credentials['password'],
			'email' => 'testamc@test.org',
			'companyName' => 'Best AMC Ever!',
			'address1' => '123 Wall Str.',
			'address2' => '124B Wall Str.',
			'city' => 'New York',
			'zip' => '44211',
			'state' => 'NY',
			'lenders' => 'VMX, TTT, abc',
			'phone' => '(423) 553-1211',
			'fax' => '(423) 553-1212'

		]);

		$response = $this->send($request, 'Unable to register the AMC user with the "'
			.$credentials['username'].'" username and the "'.$credentials['password'].'" password.');

        $id = $response->getBody()['id'];

		$request = Request::patch('/amcs/'.$id, [
			'status' => Status::APPROVED
		]);

		$admin = $this->config->get('qa.integrations.sessions.credentials.admin');

		$admin = $this->login($admin['username'], $admin['password']);

		$request->addHeader('token', $admin['token']);

		$this->send($request, 'Unable to approve the AMC user.', Response::HTTP_NO_CONTENT);

        return $id;
	}

	/**
	 * @param int $customerId
	 */
	private function createJobTypes($customerId)
	{
		/**
		 * @var Local[] $locals
		 */
		$locals = $this->entityManager->getRepository(Local::class)->findAll();

		/**
		 * @var Customer $customer
		 */
		$customer = $this->entityManager->getReference(Customer::class, $customerId);


		foreach ($locals as $local){
			if (in_array($local->getId(), [1])){
				continue ;
			}

			$jobType = new JobType();
			$jobType->setTitle('Form #'.$local->getId());
			$jobType->setCustomer($customer);
			$jobType->setLocal($local);

			$this->entityManager->persist($jobType);
		}

		$this->entityManager->flush();
	}

	/**
	 * @param int $customerId
	 * @param string $token
	 */
	private function createClients($customerId, $token)
	{

		//ID=1
		$request = Request::post('/customers/'.$customerId.'/clients', [
			'name' => 'Good Client',
			'address1' => '1st Street',
			'address2' => '2nd Street',
			'city' => 'San Pedro',
			'state' => 'FL',
			'zip' => '59322',
		]);

		$request->setAuth($token);

		$this->send($request, 'Unable to create "Good Client".');

		//ID=2
		$request = Request::post('/customers/'.$customerId.'/clients', [
			'name' => 'Best Client',
			'address1' => '3rd Street',
			'address2' => '4th Street',
			'city' => 'Los Animals',
			'state' => 'CA',
			'zip' => '95222',
		]);

		$request->setAuth($token);

		$this->send($request, 'Unable to create "Best Client".');

		//ID=3
		$request = Request::post('/customers/'.$customerId.'/clients', [
			'name' => 'Good Client 1',
			'address1' => '4th Street',
			'address2' => '5th Street',
			'city' => 'San Ocean',
			'state' => 'CA',
			'zip' => '94132',
		]);

		$request->setAuth($token);

		$this->send($request, 'Unable to create "Good Client 1".');

		//ID=4
		$request = Request::post('/customers/'.$customerId.'/clients', [
			'name' => 'Best Client 1',
			'address1' => '6th Street',
			'address2' => '7th Street',
			'city' => 'Los Food',
			'state' => 'TX',
			'zip' => '88888',
		]);

		$request->setAuth($token);

		$this->send($request, 'Unable to create "Best Client 1".');
	}
}
