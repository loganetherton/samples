<?php
namespace ValuePad\Debug\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Debug\Processors\LinkProcessor;

class LinkController extends BaseController
{
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function initialize(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	public function store(LinkProcessor $processor)
	{
		/**
		 * @var Customer $customer
		 */
		$customer = $this->entityManager->find(Customer::class, $processor->getCustomer());

		/**
		 * @var Appraiser $appraiser
		 */
		$appraiser = $this->entityManager->find(Appraiser::class, $processor->getAppraiser());

		$customer->addAppraiser($appraiser);

		$this->entityManager->flush();
	}
}
