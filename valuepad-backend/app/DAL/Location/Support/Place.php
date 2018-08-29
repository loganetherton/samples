<?php
namespace ValuePad\DAL\Location\Support;
use DateTime;

class Place
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) {  $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var string
     */
    private $address;
    public function setAddress($address) { $this->address = $address; }
    public function getAddress() { return $this->address; }

    /**
     * @var string
     */
    private $latitude;
    public function setLatitude($latitude) { $this->latitude = $latitude; }
    public function getLatitude() { return $this->latitude; }

    /**
     * @var string
     */
    private $longitude;
    public function setLongitude($longitude) { $this->longitude = $longitude; }
    public function getLongitude() { return $this->longitude; }

    /**
     * @var Error
     */
    private $error;
    public function setError(Error $error = null) { $this->error = $error; }
    public function getError() { return $this->error; }

    /**
     * @var string
     */
    private $message;
    public function setMessage($message) { $this->message = $message; }
    public function getMessage() { return $this->message; }

    /**
     * @var int
     */
    private $attempts = 0;
    public function setAttempts($attempts) { $this->attempts = $attempts; }
    public function getAttempts() { return $this->attempts; }
    public function addAttempt() { $this->attempts ++; }

    /**
     * @var DateTime
     */
    private $createdAt;
    public function setCreatedAt(DateTime $datetime) { $this->createdAt = $datetime; }
    public function getCreatedAt() { return $this->createdAt; }

    /**
     * @var DateTime
     */
    private $updatedAt;
    public function setUpdatedAt(DateTime $datetime) { $this->updatedAt = $datetime; }
    public function getUpdatedAt() { return $this->updatedAt; }

    public function __construct()
    {
        $this->setCreatedAt(new DateTime());
        $this->setUpdatedAt(new DateTime());
    }
}
