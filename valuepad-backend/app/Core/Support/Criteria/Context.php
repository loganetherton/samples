<?php
namespace ValuePad\Core\Support\Criteria;

class Context
{
	/**
	 * @var Join[]
	 */
	private $joins = [];

	/**
	 * @param Join $join
	 */
	public function addJoin(Join $join)
	{
		$this->joins[(string) $join] = ['reference' => $join, 'used' => false];
	}

	/**
	 * @param Join $join
	 */
	public function useJoin(Join $join)
	{
		$this->joins[(string) $join]['used'] = true;
	}

	/**
	 * @param Join $join
	 * @return string
	 */
	public function hasJoin(Join $join)
	{
		return isset($this->joins[(string) $join]);
	}

	/**
	 * @return Join[]
	 */
	public function getUnusedJoins()
	{
		return array_map(function($data){
			return $data['reference'];
		}, array_filter($this->joins, function($data){
			return $data['used'] == false;
		}));
	}
}
