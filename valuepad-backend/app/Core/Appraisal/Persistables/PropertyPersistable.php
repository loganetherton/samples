<?php
namespace ValuePad\Core\Appraisal\Persistables;

use ValuePad\Core\Appraisal\Enums\Property\BestPersonToContact;
use ValuePad\Core\Appraisal\Enums\Property\Occupancy;
use ValuePad\Core\Appraisal\Enums\Property\OwnerInterest;
use ValuePad\Core\Appraisal\Enums\Property\OwnerInterests;
use ValuePad\Core\Appraisal\Enums\Property\ValueQualifiers;
use ValuePad\Core\Appraisal\Enums\Property\ValueTypes;

class PropertyPersistable
{
	/**
	 * @var string
	 */
	private $type;
	public function setType($type) { $this->type = $type; }
	public function getType() { return $this->type; }

	/**
	 * @var string
	 */
	private $viewType;
	public function setViewType($type) { $this->viewType = $type; }
	public function getViewType() { return $this->viewType; }

    /**
     * @var string[]
     */
    private $characteristics;
    public function setCharacteristics(array $characteristics) { $this->characteristics = $characteristics; }
    public function getCharacteristics() { return $this->characteristics; }

	/**
	 * @var float
	 */
	private $approxBuildingSize;
	public function setApproxBuildingSize($size) { $this->approxBuildingSize = $size; }
	public function getApproxBuildingSize() { return $this->approxBuildingSize; }

	/**
	 * @var float
	 */
	private $approxLandSize;
	public function setApproxLandSize($size) { $this->approxLandSize = $size; }
	public function getApproxLandSize() { return $this->approxLandSize; }

	/**
	 * @var int
	 */
	private $buildingAge;
	public function setBuildingAge($age) { $this->buildingAge = $age; }
	public function getBuildingAge() { return $this->buildingAge; }

	/**
	 * @var int
	 */
	private $numberOfStories;
	public function setNumberOfStories($number) { $this->numberOfStories = $number; }
	public function getNumberOfStories() { return $this->numberOfStories; }

	/**
	 * @var int
	 */
	private $numberOfUnits;
	public function setNumberOfUnits($property) { $this->numberOfUnits = $property; }
	public function getNumberOfUnits() { return $this->numberOfUnits; }

	/**
	 * @var float
	 */
	private $grossRentalIncome;
	public function setGrossRentalIncome($amount) { $this->grossRentalIncome = $amount; }
	public function getGrossRentalIncome() { return $this->grossRentalIncome; }

	/**
	 * @var float
	 */
	private $incomeSalesCost;
	public function setIncomeSalesCost($amount) { $this->incomeSalesCost = $amount; }
	public function getIncomeSalesCost() { return $this->incomeSalesCost; }


	/**
	 * @var ValueTypes
	 */
	private $valueTypes;
	public function setValueTypes(ValueTypes $valueTypes) { $this->valueTypes = $valueTypes; }
	public function getValueTypes() { return $this->valueTypes; }

	/**
	 * @var ValueQualifiers
	 */
	private $valueQualifiers;
	public function setValueQualifiers(ValueQualifiers $qualifiers) { $this->valueQualifiers = $qualifiers; }
	public function getValueQualifiers() { return $this->valueQualifiers; }

    /**
     * @param OwnerInterest $interest
     */
	public function setOwnerInterest(OwnerInterest $interest = null)
    {
        $interests = new OwnerInterests();

        if ($interest !== null){
            $interests->push($interest);
        }

        $this->setOwnerInterests($interests);
    }


    /**
     * @var OwnerInterests
     */
    private $ownerInterests;
    public function setOwnerInterests(OwnerInterests $interests) { $this->ownerInterests = $interests; }
    public function getOwnerInterests() { return $this->ownerInterests; }

	/**
	 * @var string
	 */
	private $address1;
	public function setAddress1($address) { $this->address1 = $address; }
	public function getAddress1() { return $this->address1; }

	/**
	 * @var string
	 */
	private $address2;
	public function setAddress2($address) { $this->address2 = $address; }
	public function getAddress2() { return $this->address2; }

	/**
	 * @var string
	 */
	private $city;
	public function setCity($city) { $this->city = $city; }
	public function getCity() { return $this->city; }

	/**
	 * @var string
	 */
	private $zip;
	public function setZip($zip) { $this->zip = $zip; }
	public function getZip() { return $this->zip; }

	/**
	 * @var int
	 */
	private $county;
	public function setCounty($county) { $this->county = $county; }
	public function getCounty() { return $this->county; }


	/**
	 * @var string
	 */
	private $state;
	public function setState($state) { $this->state = $state; }
	public function getState() { return $this->state; }

	/**
	 * @var Occupancy
	 */
	private $occupancy;
	public function setOccupancy(Occupancy $occupancy) { $this->occupancy = $occupancy; }
	public function getOccupancy() { return $this->occupancy; }

	/**
	 * @var BestPersonToContact
	 */
	private $bestPersonToContact;
	public function getBestPersonToContact() { return $this->bestPersonToContact; }
	public function setBestPersonToContact(BestPersonToContact $type) { $this->bestPersonToContact = $type; }

	/**
	 * @var string
	 */
	private $legal;
	public function setLegal($legal) { $this->legal = $legal; }
	public function getLegal() { return $this->legal; }

	/**
	 * @var string
	 */
	private $additionalComments;
	public function setAdditionalComments($comments) { $this->additionalComments = $comments; }
	public function getAdditionalComments() { return $this->additionalComments; }

	/**
	 * @var ContactPersistable[]
	 */
	private $contacts;
	public function setContacts(array $contacts) { $this->contacts = $contacts; }
	public function getContacts() { return $this->contacts; }
}
