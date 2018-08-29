<?php
namespace ValuePad\Core\Appraisal\Options;

class CreateOrderOptions
{
    /**
     * @var int
     */
    private $fromStaff;

    /**
     * @var int
     */
    private $companyId;


    /**
     * @param bool $flag
     * @return $this
     */
	public function setFromStaff($flag)
    {
        $this->fromStaff = $flag;
    }

    /**
     * @return int
     */
    public function isFromStaff()
    {
        return $this->fromStaff;
    }

    /**
     * @param int $companyId
     * @return $this
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }
}
