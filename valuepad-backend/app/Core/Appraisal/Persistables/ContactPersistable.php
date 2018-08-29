<?php
namespace ValuePad\Core\Appraisal\Persistables;

use ValuePad\Core\Appraisal\Enums\Property\ContactType;
use DateTime;

class ContactPersistable
{
	/**
	 * @var ContactType
	 */
	private $type;
	public function setType(ContactType $type) { $this->type = $type; }
	public function getType() { return $this->type; }

	/**
	 * @var string
	 */
	private $firstName;
	public function getFirstName() { return $this->firstName; }
	public function setFirstName($firstName) { $this->firstName = $firstName; }

	/**
	 * @var string
	 */
	private $lastName;
	public function getLastName() { return $this->lastName; }
	public function setLastName($lastName) { $this->lastName = $lastName; }

	/**
	 * @var string
	 */
	private $middleName;
	public function setMiddleName($name) { $this->middleName = $name; }
	public function getMiddleName() { return $this->middleName; }

	/**
	 * @var string
	 */
	private $homePhone;
	public function setHomePhone($phone) { $this->homePhone = $phone; }
	public function getHomePhone() { return $this->homePhone; }

	/**
	 * @var string
	 */
	private $workPhone;
	public function setWorkPhone($phone) { $this->workPhone = $phone; }
	public function getWorkPhone() { return $this->workPhone; }

	/**
	 * @var string
	 */
	private $cellPhone;
	public function setCellPhone($phone) { $this->cellPhone = $phone; }
	public function getCellPhone() { return $this->cellPhone; }

	/**
	 * @var string
	 */
	private $email;
	public function setEmail($email) { $this->email = $email; }
	public function getEmail() { return $this->email; }

	/**
	 * @var string
	 */
	private $name;
	public function setName($name) { $this->name = $name; }
	public function getName() { return $this->name; }

    /**
     * @var DateTime
     */
    private $intentProceedDate;
    public function setIntentProceedDate(DateTime $datetime) { $this->intentProceedDate = $datetime; }
    public function getIntentProceedDate() { return $this->intentProceedDate; }
}
