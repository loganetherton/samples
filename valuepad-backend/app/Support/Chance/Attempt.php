<?php
namespace ValuePad\Support\Chance;
use DateTime;

class Attempt
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var string
     */
    private $tag;
    public function setTag($tag) { $this->tag = $tag; }
    public function getTag() { return $this->tag; }

    /**
     * @var array
     */
    private $data;
    public function setData(array $data) { $this->data = $data; }
    public function getData() { return $this->data; }

    /**
     * @var int
     */
    private $quantity = 0;
    public function getQuantity() { return $this->quantity; }
    public function increaseQuantity() { $this->quantity ++; }

    /**
     * @var DateTime
     */
    private $createdAt;
    public function setCreatedAt(DateTime $datetime) { $this->createdAt = $datetime; }
    public function getCreatedAt() { return $this->createdAt; }

    /**
     * @var DateTime
     */
    private $attemptedAt;
    public function setAttemptedAt(DateTime $datetime) { $this->attemptedAt = $datetime; }
    public function getAttemptedAt() { return $this->attemptedAt; }

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }
}
