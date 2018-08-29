<?php
namespace ValuePad\Core\Customer\Persistables;

use ValuePad\Core\Customer\Enums\CompanyType;
use ValuePad\Core\User\Persistables\UserPersistable;

class CustomerPersistable extends UserPersistable
{
	/**
	 * @var string
	 */
	private $name;
	public function setName($name) { $this->name = $name; }
	public function getName() { return $this->name; }


	/**
	 * @var string
	 */
	private $phone;
	public function getPhone() { return $this->phone; }
	public function setPhone($phone) { $this->phone = $phone; }

	/**
	 * @var CompanyType
	 */
	private $companyType;
	public function getCompanyType() { return $this->companyType; }
	public function setCompanyType(CompanyType $companyType) { $this->companyType = $companyType; }
}
