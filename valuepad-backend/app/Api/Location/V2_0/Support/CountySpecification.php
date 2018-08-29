<?php
namespace ValuePad\Api\Location\V2_0\Support;

class CountySpecification
{
	public function __invoke($zips)
	{
		$result = [];

		foreach ($zips as $zip){
			$result[] = $zip->getCode();
		}

		return $result;
	}
}
