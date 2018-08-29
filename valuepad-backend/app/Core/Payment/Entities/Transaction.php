<?php
namespace ValuePad\Core\Payment\Entities;
use ValuePad\Core\Payment\Enums\Status;
use DateTime;
use ValuePad\Core\User\Entities\User;

class Transaction
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var User
     */
    private $owner;
    public function setOwner(User $owner) { $this->owner = $owner; }
    public function getOwner() { return $this->owner; }

    /**
     * @var string
     */
    private $externalId;
    public function setExternalId($id) { $this->externalId = $id; }
    public function getExternalId() { return $this->externalId; }

    /**
     * @var Status $status
     */
    private $status;
    public function setStatus(Status $status) { $this->status = $status; }
    public function getStatus() { return $this->status; }

    /**
     * @var string
     */
    private $message;
    public function setMessage($message) { $this->message = $message; }
    public function getMessage() { return $this->message; }

    /**
     * @var DateTime
     */
    private $createdAt;
    public function setCreatedAt(DateTime $datetime) { $this->createdAt = $datetime; }
    public function getCreatedAt() { return $this->createdAt; }

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }
}
