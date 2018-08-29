<?php
namespace ValuePad\Core\Company\Entities;

use ValuePad\Core\Asc\Entities\AscAppraiser;
use ValuePad\Core\Invitation\Enums\Requirements;
use ValuePad\Core\Invitation\Properties\RequirementsPropertyTrait;

class Invitation
{
    use RequirementsPropertyTrait;

    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var AscAppraiser
     */
    private $ascAppraiser;
    public function setAscAppraiser(AscAppraiser $ascAppraiser) { $this->ascAppraiser = $ascAppraiser; }
    public function getAscAppraiser() { return $this->ascAppraiser; }

    /**
     * @var string
     */
    private $phone;
    public function setPhone($phone) { $this->phone = $phone; }
    public function getPhone() { return $this->phone; }

    /**
     * @var string
     */
    private $email;
    public function setEmail($email) { $this->email = $email; }
    public function getEmail() { return $this->email; }

    /**
     * @var Branch
     */
    private $branch;
    public function setBranch(Branch $branch) { $this->branch = $branch; }
    public function getBranch() { return $this->branch; }

    public function __construct()
    {
        $this->setRequirements(new Requirements());
    }
}
