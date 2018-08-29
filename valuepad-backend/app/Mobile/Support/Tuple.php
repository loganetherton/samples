<?php
namespace ValuePad\Mobile\Support;

use ValuePad\Core\User\Entities\User;

class Tuple
{
	/**
	 * @var User[]
	 */
	private $users;

	/**
	 * @var News
	 */
	private $news;

	/**
	 * @param User[] $users
	 * @param News $news
	 */
	public function __construct(array $users, News $news)
	{
		$this->users = $users;
		$this->news = $news;
	}

	/**
	 * @return array|User[]
	 */
	public function getUsers()
	{
		return $this->users;
	}

	/**
	 * @return News
	 */
	public function getNews()
	{
		return $this->news;
	}
}
