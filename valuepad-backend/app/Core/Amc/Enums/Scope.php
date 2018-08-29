<?php
namespace ValuePad\Core\Amc\Enums;
use Ascope\Libraries\Enum\Enum;

class Scope extends Enum
{
    const NORMAL = 'normal';
    const BY_STATE = 'by-state';
    const BY_COUNTY = 'by-county';
    const BY_ZIP = 'by-zip';
}
