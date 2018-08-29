<?php
namespace ValuePad\Core\Assignee\Persistables;

class NotificationSubscriptionPersistable
{
	/**
	 * @var bool
	 */
	private $email;
	public function getEmail() { return $this->email; }
	public function setEmail($email) { $this->email = $email; }}
