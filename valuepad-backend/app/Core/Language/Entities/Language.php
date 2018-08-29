<?php
namespace ValuePad\Core\Language\Entities;

use ValuePad\Core\Shared\Properties\NamePropertyTrait;

class Language
{
	use NamePropertyTrait;

    /**
     * @var string
     */
    private $code;

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
