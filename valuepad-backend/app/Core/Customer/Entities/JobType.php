<?php
namespace ValuePad\Core\Customer\Entities;

use ValuePad\Core\JobType\Entities\JobType as Local;

class JobType
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }


    /**
     * @var bool
     */
    private $isCommercial;
    public function setCommercial($flag) { $this->isCommercial = $flag; }
    public function isCommercial() { return $this->isCommercial; }

    /**
     * @var bool
     */
    private $isPayable;
    public function setPayable($flag) { $this->isPayable = $flag; }
    public function isPayable() { return $this->isPayable; }


    /**
     * @var string
     */
    private $title;
    public function setTitle($title) { $this->title = $title; }
    public function getTitle() { return $this->title; }

    /**
     * @var Customer
     */
    private $customer;
    public function setCustomer(Customer $customer) { $this->customer = $customer; }
    public function getCustomer() { return $this->customer; }

	/**
	 * @var Local
	 */
	private $local;
    public function setLocal(Local $local = null) { $this->local = $local; }
    public function getLocal() { return $this->local; }

	/**
	 * @var bool
	 */
	private $isHidden = false;
    public function setHidden($flag) { $this->isHidden = $flag; }
    public function isHidden() { return $this->isHidden; }

	public function __construct()
	{
		$this->setPayable(true);
		$this->setCommercial(false);
	}
}
