<?php
namespace ValuePad\Core\Location\Properties;

/**
 *
 *
 */
trait StateAsCodePropertyTrait
{
    /**
     * @var string
     */
    private $state;

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }
}
