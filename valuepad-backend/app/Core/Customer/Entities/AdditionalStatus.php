<?php
namespace ValuePad\Core\Customer\Entities;

class AdditionalStatus
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var string
     */
    private $title;
    public function setTitle($title) { $this->title = $title; }
    public function getTitle() { return $this->title; }

    /**
     * @var string
     */
    private $comment;
    public function setComment($comment) { $this->comment = $comment; }
    public function getComment() { return $this->comment; }

    /**
     * @var Customer
     */
    private $customer;
    public function setCustomer(Customer $customer) { $this->customer = $customer; }
    public function getCustomer() { return $this->customer; }

	/**
	 * @var bool
	 */
	private $isActive = true;
	public function setActive($flag) { $this->isActive = $flag; }
	public function isActive() { return $this->isActive; }
}
