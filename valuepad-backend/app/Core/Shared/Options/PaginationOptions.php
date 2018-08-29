<?php
namespace ValuePad\Core\Shared\Options;

class PaginationOptions
{
	/**
	 * @var int
	 */
	private $page;

	/**
	 * @var int
	 */
	private $perPage;

	public function __construct($page = 1, $perPage = 10)
	{
		$this->setPage($page);
		$this->setPerPage($perPage);
	}

	public function setPage($page)
	{
		$this->page = $page;
	}

	/**
	 * @return int
	 */
	public function getOffset()
	{
		return ($this->getPage() - 1) * $this->getPerPage();
	}

	/**
	 * @return int
	 */
	public function getPage()
	{
		return $this->page;
	}

	/**
	 * @param int $perPage
	 */
	public function setPerPage($perPage)
	{
		$this->perPage = $perPage;
	}

	/**
	 * @return int
	 */
	public function getPerPage()
	{
		return $this->perPage;
	}
}
