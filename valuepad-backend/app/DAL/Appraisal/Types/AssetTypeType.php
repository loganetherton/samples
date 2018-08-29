<?php
namespace ValuePad\DAL\Appraisal\Types;
use ValuePad\Core\Appraisal\Enums\AssetType;
use ValuePad\DAL\Support\EnumType;

class AssetTypeType extends EnumType
{
    /**
     * @return string
     */
    protected function getEnumClass()
    {
        return AssetType::class;
    }
}
