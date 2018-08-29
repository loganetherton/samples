<?php
namespace ValuePad\Core\Appraisal\Services;

use Ascope\Libraries\Validation\PresentableException;
use ValuePad\Core\Appraisal\Entities\Bid;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Exceptions\OperationNotPermittedWithCurrentProcessStatusException;
use ValuePad\Core\Appraisal\Notifications\SubmitBidNotification;
use ValuePad\Core\Appraisal\Options\CreateBidOptions;
use ValuePad\Core\Appraisal\Options\UpdateBidOptions;
use ValuePad\Core\Appraisal\Persistables\BidPersistable;
use ValuePad\Core\Appraisal\Validation\BidValidator;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Support\Service\AbstractService;

class BidService extends AbstractService
{
	use CommonsTrait;

	/**
	 * @param int $orderId
	 * @return Bid
	 */
	public function get($orderId)
	{
		return $this->entityManager->getRepository(Bid::class)->findOneBy(['order' => $orderId]);
	}

	/**
	 * @param int $orderId
	 * @param BidPersistable $persistable
	 * @param CreateBidOptions $options
	 * @return Bid
	 */
	public function create($orderId, BidPersistable $persistable, CreateBidOptions $options = null)
	{
		if ($options === null){
			$options = new CreateBidOptions();
		}

		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $orderId);

		if (!$order->getProcessStatus()->is(ProcessStatus::REQUEST_FOR_BID)){
			throw  new OperationNotPermittedWithCurrentProcessStatusException();
		}

		if ($order->getBid()){
			throw new PresentableException('The bid has been already created for this order.');
		}

		(new BidValidator($this->environment, $this->container))
			->requireEstimatedCompletionDate($options->isEstimatedCompletionDateRequired())
			->setValidateAppraisers((bool) $order->getStaff())
			->setCompany($order->getCompany())
			->validate($persistable);


		$this->handleInvitationInOrder($order, $this->container);

		$bid = new Bid();

		$bid->setOrder($order);

		$this->exchange($persistable, $bid);

		$this->entityManager->persist($bid);

		$this->entityManager->flush();

		$this->notify(new SubmitBidNotification($order));

		return $bid;
	}

	/**
	 * @param int $orderId
	 * @param BidPersistable $persistable
	 * @param UpdateBidOptions $options
	 */
	public function update($orderId, BidPersistable $persistable, UpdateBidOptions $options = null)
	{
		if ($options === null){
			$options = new UpdateBidOptions();
		}

		$order = $this->entityManager->find(Order::class, $orderId);

		(new BidValidator($this->environment, $this->container))
			->requireEstimatedCompletionDate($options->isEstimatedCompletionDateRequired())
			->setForcedProperties($options->getPropertiesScheduledToClear())
			->setValidateAppraisers((bool) $order->getStaff())
			->setCompany($order->getCompany())
			->validate($persistable, true);

		/**
		 * @var Bid $bid
		 */
		$bid = $this->entityManager->getRepository(Bid::class)->findOneBy(['order' => $orderId]);

		$this->exchange($persistable, $bid, ['nullable' => $options->getPropertiesScheduledToClear()]);

		$this->entityManager->flush();
	}

	/**
	 * @param BidPersistable $persistable
	 * @param Bid $bid
	 * @param array $config
	 */
	private function exchange(BidPersistable $persistable, Bid $bid, array $config = [])
	{
		$this->transfer($persistable, $bid, array_merge(['ignore' => [
			'appraisers'
		]], $config));

		if ($persistable->getAppraisers() && $bid->getOrder()->getCompany()) {
			$em = $this->entityManager;
			$appraisers = array_map(function ($appraiserId) use ($em) {
				return $em->find(Appraiser::class, $appraiserId);
			}, $persistable->getAppraisers());

			$bid->setAppraisers($appraisers);
		}
	}
}
