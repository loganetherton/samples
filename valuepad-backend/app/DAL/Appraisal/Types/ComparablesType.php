<?php
namespace ValuePad\DAL\Appraisal\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use ValuePad\Core\Appraisal\Objects\Comparable;
use ValuePad\DAL\Support\AbstractType;
use DateTime;

class ComparablesType extends AbstractType
{
	/**
	 * @param array $fieldDeclaration
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return 'TEXT';
	}

	/**
	 * @param string $value
	 * @param AbstractPlatform $platform
	 * @return Comparable[]
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		if ($value === null) {
			return null;
		}

		$data = json_decode($value, true);
		$result = [];

		foreach ($data as $row){
			$comparable = new Comparable();
			$comparable->setComment(array_take($row, 'comment'));
			$comparable->setActualAge(array_take($row, 'actualAge'));
			$comparable->setAddress(array_take($row, 'address'));

			$closedDate = array_take($row, 'closedDate');

			if ($closedDate){
				$comparable->setClosedDate(new DateTime($closedDate));
			}

			$comparable->setDistanceToSubject(array_take($row, 'distanceToSubject'));
			$comparable->setSalesPrice(array_take($row, 'salesPrice'));
			$comparable->setLivingArea(array_take($row, 'livingArea'));
			$comparable->setSiteSize(array_take($row, 'siteSize'));
			$comparable->setSourceData(array_take($row, 'sourceData'));

			$result[] = $comparable;
		}

		return $result;
	}

	/**
	 * @param Comparable[] $value
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		if ($value === null) {
			return null;
		}

		$data = [];

		foreach ($value as $comparable){
			$row = [
				'comment' => $comparable->getComment(),
				'actualAge' => $comparable->getActualAge(),
				'address' => $comparable->getAddress(),
				'distanceToSubject' => $comparable->getDistanceToSubject(),
				'salesPrice' => $comparable->getSalesPrice(),
				'livingArea' => $comparable->getLivingArea(),
				'siteSize' => $comparable->getSiteSize(),
				'sourceData' => $comparable->getSourceData(),
				'closedDate' => null
			];

			if ($comparable->getClosedDate()){
				$row['closedDate'] = $comparable->getClosedDate()->format(DateTime::ATOM);
			}

			$data[] = $row;
		}

		return json_encode($data);
	}
}
