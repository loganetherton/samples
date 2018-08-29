<?php
namespace ValuePad\Core\Location\Services;

use ValuePad\Core\Location\Entities\County;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Support\Service\AbstractService;

class StateService extends AbstractService
{
    /**
     * @return State[]
     */
    public function getAll()
    {
        return $this->entityManager->getRepository(State::class)->findAll();
    }

    /**
     * @param string $code
     * @return bool
     */
    public function exists($code)
    {
        return $this->entityManager->getRepository(State::class)->exists(['code' => $code]);
    }

	/**
	 * @param array $codes
	 * @return bool
	 */
	public function existSelected(array $codes)
	{
		$total = $this->entityManager->getRepository(State::class)
			->count(['code' => ['in', $codes]]);

		return count($codes) === $total;
	}

	/**
	 * @param string $state
	 * @param int $countyId
	 * @return bool
	 */
	public function hasCounty($state, $countyId)
	{
		return $this->entityManager
			->getRepository(County::class)
			->exists(['state' => $state, 'id' => $countyId]);
	}
}
