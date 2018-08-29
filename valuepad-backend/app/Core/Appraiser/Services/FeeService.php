<?php
namespace ValuePad\Core\Appraiser\Services;

use ValuePad\Core\Appraiser\Entities\DefaultFee;
use ValuePad\Core\Assignee\Objects\Total;
use ValuePad\Core\Assignee\Entities\CustomerFee;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Support\Service\AbstractService;

class FeeService extends AbstractService
{
	/**
	 * @param int $appraiserId
	 * @return Total[]
	 */
	public function getTotals($appraiserId)
	{
		$enabledDefault = $this->entityManager->getRepository(DefaultFee::class)
			->count(['appraiser' => $appraiserId]);


		$totals = [];

		$defaultTotal = new Total();
		$defaultTotal->setEnabled($enabledDefault);

		$totals[] = $defaultTotal;


		$builder = $this->entityManager->createQueryBuilder();

		$data = $builder->select('COUNT(f.id) AS total', 'IDENTITY(f.customer) AS customer')
			->from(CustomerFee::class, 'f')
			->where($builder->expr()->eq('f.assignee', ':appraiser'))
			->groupBy('f.customer')
			->setParameter('appraiser', $appraiserId)
			->getQuery()
			->getResult();

		$map = [];

		foreach ($data as $row){
			$map[$row['customer']] = (int) $row['total'];
		}

		/**
		 * @var Customer[] $customers
		 */
		$customers = $this->entityManager->getRepository(Customer::class)
			->retrieveAll(['id' => ['in', array_keys($map)]]);

		foreach ($customers as $customer){
			$total = new Total();
			$total->setEnabled($map[$customer->getId()]);
			$total->setCustomer($customer);
			$totals[] = $total;
		}

		return $totals;
	}
}
