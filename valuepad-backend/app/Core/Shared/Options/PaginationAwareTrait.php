<?php
namespace ValuePad\Core\Shared\Options;

trait PaginationAwareTrait
{
	/**
	 * @var PaginationOptions
	 */
	private $pagination;

	/**
	 * @param PaginationOptions $options
	 */
	public function setPagination(PaginationOptions $options)
	{
		$this->pagination = $options;
	}

	/**
	 * @return PaginationOptions
	 */
	public function getPagination()
	{
		if ($this->pagination === null){
			$this->pagination = new PaginationOptions();
		}

		return $this->pagination;
	}
}
