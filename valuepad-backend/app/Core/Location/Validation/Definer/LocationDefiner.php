<?php
namespace ValuePad\Core\Location\Validation\Definer;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\SourceHandlerInterface;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Inflators\StateInflator;
use ValuePad\Core\Location\Validation\Rules\CountyExistsInState;
use ValuePad\Core\Location\Validation\Rules\Zip;

class LocationDefiner
{
    /**
     * @var string
     */
    private $holder;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var bool
     */
    private $withCounty = false;

    /**
     * @var StateService
     */
    private $stateService;

    /**
     * @var bool
     */
    private $obligate = true;

    /**
     * @var bool
     */
    private $singleAddress = false;

    /**
     * @var bool
     */
    private $isHolderOptional = false;

    /**
     * @param StateService $stateService
     */
    public function __construct(StateService $stateService)
    {
        $this->stateService = $stateService;
    }

    /**
     * @param Binder $binder
     */
    public function define(Binder $binder)
    {
        $binder->bind($this->prepareProperty($this->singleAddress ? 'address' : 'address1'), function (Property $property) {

            if ($this->obligate){
                $property->addRule(new Obligate());
            }

            $property->addRule(new Blank());
        })->when([$this, 'checkHolderExistence']);

        if (!$this->singleAddress){
            $binder->bind($this->prepareProperty('address2'), function (Property $property) {
                $property->addRule(new Blank());
            })->when([$this, 'checkHolderExistence']);
        }

        $binder->bind($this->prepareProperty('city'), function (Property $property) {

            if ($this->obligate){
                $property->addRule(new Obligate());
            }

            $property->addRule(new Blank());
        })->when([$this, 'checkHolderExistence']);

        $binder->bind($this->prepareProperty('zip'), function (Property $property) {

            if ($this->obligate){
                $property->addRule(new Obligate());
            }

            $property->addRule(new Zip());
        })->when([$this, 'checkHolderExistence']);

        $binder
            ->bind($this->prepareProperty('state'), (new StateInflator($this->stateService))->setRequired($this->obligate))
            ->when([$this, 'checkHolderExistence']);

        if ($this->withCounty){
            $county = $this->prepareProperty('county');
            $state = $this->prepareProperty('state');

            $binder->bind($county, [$county, $state],
                function(Property $property){
                    $property
                        ->addRule(new CountyExistsInState($this->stateService));
                }
            )->when([$this, 'checkHolderExistence']);
        }
    }

    /**
     * @param string $name
     * @return string
     */
    private function prepareProperty($name)
    {
        if ($this->prefix) {
            $name = $this->prefix.ucfirst($name);
        }

        if ($this->holder){
            $name = $this->holder.'.'.$name;
        }

        return $name;
    }

    /**
     * @param string $holder
     * @param $isOptional
     * @return $this
     */
    public function setHolder($holder, $isOptional = true)
    {
        $this->holder = $holder;
        $this->isHolderOptional = $isOptional;
        return $this;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function withCounty($flag)
    {
        $this->withCounty = $flag;
        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setObligate($flag)
    {
        $this->obligate = $flag;

        return $this;
    }

    /**
     * @param string $address
     * @return $this
     */
    public function setSingleAddress($address)
    {
        $this->singleAddress = $address;
        return $this;
    }

    /**
     * Checks whether the specified holder exists in the input.
     * Always returns true if holder is not provided.
     *
     * @param SourceHandlerInterface $source
     * @return bool
     */
    public function checkHolderExistence(SourceHandlerInterface $source)
    {
        if (!$this->holder) {
            return true;
        }

        if ($this->isHolderOptional === false){
            return true;
        }

        return $source->hasProperty($this->holder) && $source->getValue($this->holder) !== null;
    }
}
