<?php
namespace ValuePad\Core\Asc\Enums;

use Ascope\Libraries\Enum\Enum;

class Certification extends Enum
{
    const LICENSED = 'licensed';
	const CERTIFIED_RESIDENTIAL = 'certified-residential';
    const CERTIFIED_GENERAL = 'certified-general';
	const TRANSITIONAL_LICENSE = 'transitional-license';
}
