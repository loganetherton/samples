<?php
namespace ValuePad\Core\Assignee\Entities;

use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\User\Entities\User;

class NotificationSubscription
{
	/**
	 * @var int
	 */
	private $id;
	public function setId($id) { $this->id = $id; }
	public function getId() { return $this->id; }

	/**
	 * @var Appraiser
	 */
	private $assignee;
	public function setAssignee(User $assignee) { $this->assignee = $assignee; }
	public function getAssignee() { return $this->assignee; }


	/**
	 * @var Customer
	 */
	private $customer;
	public function setCustomer(Customer $customer) { $this->customer = $customer; }
	public function getCustomer() { return $this->customer; }

	/**
	 * @var bool
	 */
	private $email;
	public function getEmail() { return $this->email; }
	public function setEmail($email) { $this->email = $email; }


	public function __construct()
	{
		$this->setEmail(true);
	}
}
