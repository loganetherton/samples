<?php
namespace ValuePad\Core\Customer\Entities;

class AdditionalDocumentType
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
	 * @var Customer
	 */
	private $customer;
	public function setCustomer(Customer $customer) { $this->customer = $customer; }
}
