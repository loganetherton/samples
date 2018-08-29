<?php
namespace ValuePad\DAL\Asc\Support\Import;

use ValuePad\Core\Asc\Enums\Certification;
use DateTime;
use ValuePad\DAL\Location\Fixtures\States;

class Row
{
	const ADDRESS = 'Street Address';
	const CITY = 'City';
	const STATE = 'State';
	const ZIP = 'Zip Code';
	const CERTIFICATION = 'License Certificate Type';
	const LAST_NAME = 'Last Name';
	const FIRST_NAME = 'First Name';
	const LICENSE_NUMBER = 'State License Number';
	const LICENSE_STATE = 'Licensing State';
	const LICENSE_EXPIRES_AT = 'Expiration Date of License';
	const COMPANY_NAME = 'Company Name';
	const PHONE = 'Telephone Number';
	const STATUS = 'Status';

	private $states = [];

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		$this->data = $data;
		$this->states = array_keys(States::getAll());
	}

	/**
	 * @return bool
	 */
	public function isActive()
	{
		return $this->getValue(static::STATUS) === 'A';
	}

	/**
	 * @return Certification
	 */
	public function getCertification()
	{
		return new Certification(strtolower(str_replace(' ', '-', $this->getValue(static::CERTIFICATION))));
	}

	/**
	 * @return string
	 */
	public function getLastName()
	{
		return $this->getValue(static::LAST_NAME);
	}

	/**
	 * @return string
	 */
	public function getFirstName()
	{
		return $this->getValue(static::FIRST_NAME);
	}

	/**
	 * @return string
	 */
	public function getAddress()
	{
		return $this->getValue(static::ADDRESS);
	}

	/**
	 * @return string
	 */
	public function getCity()
	{
		return $this->getValue(static::CITY);
	}

	/**
	 * @return string
	 */
	public function getZip()
	{
		return $this->getValue(static::ZIP);
	}

	/**
	 * @return string
	 */
	public function getState()
	{
		return $this->getStateValue(static::STATE);
	}

	/**
	 * @return string
	 */
	public function getLicenseState()
	{
		return $this->getStateValue(static::LICENSE_STATE);
	}

	/**
	 * @return string
	 */
	public function getLicenseNumber()
	{
		return $this->getValue(static::LICENSE_NUMBER);
	}

	/**
	 * @return DateTime
	 */
	public function getLicenseExpiresAt()
	{
		return new DateTime($this->getValue(static::LICENSE_EXPIRES_AT));
	}

	/**
	 * @return string
	 */
	public function getCompanyName()
	{
		return $this->getValue(static::COMPANY_NAME);
	}

	/**
	 * @return string
	 */
	public function getPhone()
	{
		$phone = $this->getValue(static::PHONE);

		if (!$phone){
			return null;
		}

		$phone = str_replace([')', '(', '-', ' ', '.', '\\', '/'], '', $phone);

		if (!is_numeric($phone)){
			return null;
		}

		// Drops the first number from the format: 1-800-333-2211
		if (strlen($phone) == 11){
			$phone = substr($phone, 1);
		}

		if (strlen($phone) != 10){
			return null;
		}

		$first = substr($phone, 0, 3);
		$second = substr($phone, 3, 3);
		$third = substr($phone, 6);

		return '('.$first.') '.$second.'-'.$third;
	}

	/**
	 * @param string $column
	 * @return string|null
	 */
	private function getValue($column)
	{
		$value = array_take($this->data, $column);

		if ($value == ''){
			return null;
		}

		return $value;
	}

	/**
	 * @param string $column
	 * @return null|string
	 */
	private function getStateValue($column){

		$state = $this->getValue($column);

		if (!$state){
			return null;
		}

		$state = strtoupper($state);


		if (!in_array($state, $this->states)){
			return null;
		}

		return $state;
	}
}
