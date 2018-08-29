<?php
namespace ValuePad\Live\Handlers;

class AwardOrderHandler extends AbstractDataAwareOrderHandler
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'award';
    }
}
