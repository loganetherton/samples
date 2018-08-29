<?php
namespace ValuePad\Core\Invitation\Persistables;

use ValuePad\Core\Invitation\Properties\RequirementsPropertyTrait;

class InvitationPersistable
{
	use RequirementsPropertyTrait;

	/**
	 * @var int
	 */
	private $ascAppraiser;

	/**
	 * @var int
	 */
	private $appraiser;

	/**
	 * @return int
	 */
	public function getAscAppraiser()
	{
		return $this->ascAppraiser;
	}

	/**
	 * @param int $appraiser
	 */
	public function setAscAppraiser($appraiser)
	{
		$this->ascAppraiser = $appraiser;
	}

	/**
	 * @param int $appraiser
	 */
	public function setAppraiser($appraiser)
	{
		$this->appraiser = $appraiser;
	}

	/**
	 * @return int
	 */
	public function getAppraiser()
	{
		return $this->appraiser;
	}
}
