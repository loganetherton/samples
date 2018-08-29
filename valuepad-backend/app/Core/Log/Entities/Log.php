<?php
namespace ValuePad\Core\Log\Entities;

use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Log\Enums\Action;
use ValuePad\Core\Log\Extras\EmptyExtra;
use ValuePad\Core\Log\Extras\ExtraInterface;
use ValuePad\Core\User\Entities\User;
use DateTime;

class Log
{
	/**
	 * @var int
	 */
	private $id;
	public function setId($id) { $this->id = $id; }
	public function getId() { return $this->id; }

	/**
	 * @var Order
	 */
	private $order;
	public function setOrder(Order $order = null) { $this->order = $order; }
	public function getOrder() { return $this->order; }

    /**
     * @var Customer
     */
	private $customer;
    public function setCustomer(Customer $customer) { $this->customer = $customer; }
    public function getCustomer() { return $this->customer; }

	/**
	 * @var Appraiser|Amc
	 */
	private $assignee;
	public function setAssignee(User $assignee) { $this->assignee = $assignee; }
	public function getAssignee() { return $this->assignee; }

	/**
	 * @var DateTime
	 */
	private $createdAt;
	public function setCreatedAt(DateTime $datetime) { $this->createdAt = $datetime; }
	public function getCreatedAt() { return $this->createdAt; }

	/**
	 * @var User
	 */
	private $user;
	public function setUser(User $user) { $this->user = $user; }
	public function getUser() { return $this->user; }

	/**
	 * @var ExtraInterface
	 */
	private $extra;
	public function getExtra() { return $this->extra; }
	public function setExtra(ExtraInterface $extra) { $this->extra = $extra; }

	/**
	 * @var Action
	 */
	private $action;
	public function setAction(Action $action) { $this->action = $action; }
	public function getAction() { return $this->action; }

	public function __construct()
	{
		$this->extra = new EmptyExtra();
	}
}
