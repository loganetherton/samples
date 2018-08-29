<?php
namespace ValuePad\Core\Invitation\Entities;

use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Invitation\Enums\Requirements;
use ValuePad\Core\Invitation\Enums\Status;
use ValuePad\Core\Invitation\Properties\RequirementsPropertyTrait;
use ValuePad\Core\Shared\Properties\CreatedAtPropertyTrait;
use ValuePad\Core\Shared\Properties\IdPropertyTrait;
use ValuePad\Core\Asc\Entities\AscAppraiser;


class Invitation
{
	use IdPropertyTrait;
	use CreatedAtPropertyTrait;
	use RequirementsPropertyTrait;

    /**
     * @var Customer
     */
    private $customer;
    public function setCustomer(Customer $customer) { $this->customer = $customer; }
    public function getCustomer() { return $this->customer; }

	/**
	 * @var Appraiser
	 */
	private $appraiser;
	public function setAppraiser(Appraiser $appraiser = null) { $this->appraiser = $appraiser; }
	public function getAppraiser() { return $this->appraiser; }

	/**
	 * @var string
	 */
	private $reference;

	/**
	 * @var AscAppraiser
	 */
	private $ascAppraiser;

	/**
	 * @var Status
	 */
	private $status;

	public function __construct()
	{
		$this->setRequirements(new Requirements());
	}

	/**
	 * @param string $reference
	 */
	public function setReference($reference)
	{
		$this->reference = $reference;
	}

	/**
	 * @return string
	 */
	public function getReference()
	{
		return $this->reference;
	}

	/**
	 * @param AscAppraiser $appraiser
	 */
	public function setAscAppraiser(AscAppraiser $appraiser)
	{
		$this->ascAppraiser = $appraiser;
	}

	/**
	 * @return AscAppraiser
	 */
	public function getAscAppraiser()
	{
		return $this->ascAppraiser;
	}

	/**
	 * @param Status $status
	 */
	public function setStatus(Status $status)
	{
		$this->status = $status;
	}

	/**
	 * @return Status
	 */
	public function getStatus()
	{
		return $this->status;
	}
}
