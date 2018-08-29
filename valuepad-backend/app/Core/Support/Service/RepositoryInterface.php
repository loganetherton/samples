<?php
namespace ValuePad\Core\Support\Service;

use Doctrine\Common\Persistence\ObjectRepository;

interface RepositoryInterface extends ObjectRepository
{
	/**
	 * @param array $criteria
	 * @return object[]
	 */
	public function retrieveAll(array $criteria);

	/**
	 * @param array $criteria
	 * @return object|null
	 */
	public function retrieve(array $criteria);

	/**
	 * @param array $criteria
	 * @return bool
	 */
	public function exists(array $criteria);

	/**
	 * @param array $criteria
	 * @return int
	 */
	public function count(array $criteria);

	/**
	 * @param array $criteria
	 */
	public function delete(array $criteria);
}
