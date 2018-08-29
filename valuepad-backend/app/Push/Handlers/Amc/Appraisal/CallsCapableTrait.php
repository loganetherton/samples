<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Push\Support\Call;

trait CallsCapableTrait
{
    /**
     * @param Amc $amc
     * @return Call[]
     */
    protected function createCalls(Amc $amc)
    {
        $url = $amc->getSettings()->getPushUrl();

        if ($url === null){
            return [];
        }

        $call = new Call();

        $call->setUrl($url);
        $call->setSecret1($amc->getSecret1());
        $call->setSecret2($amc->getSecret2());
        $call->setUser($amc);

        return [$call];
    }
}
