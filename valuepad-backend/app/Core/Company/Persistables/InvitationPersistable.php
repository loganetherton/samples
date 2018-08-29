<?php
namespace ValuePad\Core\Company\Persistables;

use ValuePad\Core\Invitation\Properties\RequirementsPropertyTrait;

class InvitationPersistable
{
    use RequirementsPropertyTrait;

    /**
     * @var int
     */
    private $ascAppraiser;
    public function setAscAppraiser($ascAppraiser) { $this->ascAppraiser = $ascAppraiser; }
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
}
