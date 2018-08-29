<?php
namespace ValuePad\Core\Location\Entities;

/**
 *
 *
 */
class Zip
{
	/**
	 * @var int
	 */
	private $id;

    /**
     * @var County
     */
    private $county;

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

    /**
     * @param County $county
     */
    public function setCounty(County $county)
    {
        $this->county = $county;
    }

    /**
     * @return County
     */
    public function getCounty()
    {
        return $this->county;
    }
}
