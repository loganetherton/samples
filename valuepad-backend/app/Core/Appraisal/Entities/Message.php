<?php
namespace ValuePad\Core\Appraisal\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use ValuePad\Core\User\Entities\User;
use DateTime;

class Message
{
	/**
	 * @var int
	 */
	private $id;
	public function setId($id) { $this->id = $id; }
	public function getId() { return $this->id; }

	/**
	 * @var DateTime
	 */
	private $createdAt;
	public function setCreatedAt(DateTime $datetime) { $this->createdAt = $datetime; }
	public function getCreatedAt() { return $this->createdAt; }

	/**
	 * @var string
	 */
	private $content;
	public function setContent($content) { $this->content = $content; }
	public function getContent() { return $this->content; }

	/**
	 * @var Order
	 */
	private $order;
	public function setOrder(Order $order) { $this->order = $order; }
	public function getOrder() { return $this->order; }

	/**
	 * @var User[]
	 */
	private $readers;
	public function addReader(User $user) { $this->readers->add($user); }

	/**
	 * @param User $user
	 * @return bool
	 */
	public function hasReader(User $user) { return $this->readers->contains($user); }
	public function getReaders() { return $this->readers; }
	public function clearReaders() { $this->readers->clear(); }

	/**
	 * @var User
	 */
	private $sender;
	public function setSender(User $user) { $this->sender = $user; }
	public function getSender() { return $this->sender; }

	public function __construct()
	{
		$this->readers = new ArrayCollection();
	}
}
