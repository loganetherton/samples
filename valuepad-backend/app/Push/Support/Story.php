<?php
namespace ValuePad\Push\Support;

use DateTime;
use ValuePad\Core\User\Entities\User;

class Story
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var array
     */
    private $request;
    public function setRequest(array $request) { $this->request = $request; }
    public function getRequest() { return $this->request; }

    /**
     * @var array
     */
    private $response;
    public function setResponse(array $response) { $this->response = $response; }
    public function getResponse() { return $this->response; }

    /**
     * @var array
     */
    private $error;
    public function setError(array $error) { $this->error = $error; }
    public function getError() { return $this->error; }

    /**
     * @var DateTime
     */
    private $createdAt;
    public function setCreatedAt(DateTime $datetime) { $this->createdAt = $datetime; }
    public function getCreatedAt() { return $this->createdAt; }

    /**
     * Response status code
     *
     * @var int
     */
    private $code;
    public function setCode($code) { $this->code = $code; }
    public function getCode() { return $this->code; }

    /**
     * @var int
     */
    private $order_id = null;
    public function setOrder($order = null) { $this->order_id = $order; }
    public function getOrder() { return $this->order_id; }

    /**
     * @var User
     */
    private $sender;
    public function setSender(User $sender) { $this->sender = $sender; }
    public function getSender() { return $this->sender; }

    /**
     * @var User
     */
    private $recipient;
    public function setRecipient(User $recipient) { $this->recipient = $recipient; }
    public function getRecipient() { return $this->recipient; }

    /**
     * @var string
     */
    private $type;
    public function setType($type) { $this->type = $type; }
    public function getType() { return $this->type; }

    /**
     * @var string
     */
    private $event;
    public function setEvent($event) { $this->event = $event; }
    public function getEvent() { return $this->event; }

    public function __construct()
    {
        $this->setCreatedAt(new DateTime());
    }
}
