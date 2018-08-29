<?php
namespace ValuePad\Core\Appraisal\Objects;

use DateTime;

class Comparable
{
	/**
	 * @var string
	 */
	private $address;
	public function getAddress() { return $this->address; }
	public function setAddress($address) { $this->address = $address; }

	/**
	 * @var float
	 */
	private $salesPrice;
	public function getSalesPrice() { return $this->salesPrice; }
	public function setSalesPrice($price) { $this->salesPrice = $price; }

	/**
	 * @var DateTime
	 */
	private $closedDate;
	public function getClosedDate() { return $this->closedDate; }
	public function setClosedDate(DateTime $datetime) { $this->closedDate = $datetime; }

	/**
	 * @var string
	 */
	private $livingArea;
	public function getLivingArea() { return $this->livingArea; }
	public function setLivingArea($livingArea) { $this->livingArea = $livingArea; }

	/**
	 * @var string
	 */
	private $siteSize;
	public function getSiteSize() { return $this->siteSize; }
	public function setSiteSize($size) { $this->siteSize = $size; }

	/**
	 * @var string
	 */
	private $actualAge;
	public function getActualAge() { return $this->actualAge; }
	public function setActualAge($age) { $this->actualAge = $age; }

	/**
	 * @var string
	 */
	private $distanceToSubject;
	public function getDistanceToSubject() { return $this->distanceToSubject; }
	public function setDistanceToSubject($distance) { $this->distanceToSubject = $distance; }

	/**
	 * @var string
	 */
	private $sourceData;
	public function getSourceData() { return $this->sourceData; }
	public function setSourceData($data) { $this->sourceData = $data; }

	/**
	 * @var string
	 */
	private $comment;
	public function getComment() { return $this->comment; }
	public function setComment($comment) { $this->comment = $comment; }
}
