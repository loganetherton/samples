<?php
namespace ValuePad\Core\Shared\Options;

trait CompanyOrdersAwareTrait
{
    /**
     * @var bool
     */
    private $withCompanyOrders;

    /**
     * @param bool $withCompanyOrders
     */
    public function setWithCompanyOrders($withCompanyOrders = true)
    {
        $this->withCompanyOrders = $withCompanyOrders;
    }

    /**
     * @return bool
     */
    public function getWithCompanyOrders()
    {
        return $this->withCompanyOrders;
    }
}
