<?php
namespace ValuePad\Core\Appraisal\Entities;

use ValuePad\Core\Appraisal\Enums\Property\ContactType;
use DateTime;


class Contact
{
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var ContactType
	 */
	private $type;
	public function setType(ContactType $type) { $this->type = $type; }
	public function getType() { return $this->type; }

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
	public function getName() { return $this->name; }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->refreshDisplayName();
    }


    /**
	 * @var string
	 */
	private $firstName;
	public function getFirstName() { return $this->firstName; }

	/**
	 * @param string $firstName
	 */
	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
		$this->refreshDisplayName();
	}

	/**
	 * @var string
	 */
	private $lastName;
	public function getLastName() { return $this->lastName; }

	/**
	 * @param string $lastName
	 */
	public function setLastName($lastName)
	{
		$this->lastName = $lastName;
		$this->refreshDisplayName();
	}

	/**
	 * @var string
	 */
	private $middleName;
	public function getMiddleName() { return $this->middleName; }

    /**
     * @param string $name
     */
    public function setMiddleName($name)
    {
        $this->middleName = $name;
        $this->refreshDisplayName();
    }

	/**
	 * @var string
	 */
	private $displayName;

    /**
     * @return string
     */
    private function refreshDisplayName()
    {
        if ($this->firstName === null && $this->lastName === null && $this->middleName === null && $this->name === null){
            $this->displayName = null;
        } elseif ($this->name === null) {
            $this->displayName = trim(preg_replace('/\s+/', ' ', $this->firstName.' '.$this->middleName.' '.$this->lastName));
        } else {
            $this->displayName = $this->name;
        }
    }

    /**
     * @return string
     */
	public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @var Property
     */
    private $property;
    public function setProperty(Property $property) { $this->property = $property; }


    /**
     * @var DateTime
     */
    private $intentProceedDate;
    public function setIntentProceedDate(DateTime $datetime = null) { $this->intentProceedDate = $datetime; }
    public function getIntentProceedDate() { return $this->intentProceedDate; }
}
