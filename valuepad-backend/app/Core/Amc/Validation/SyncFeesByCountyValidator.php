<?php
namespace ValuePad\Core\Amc\Validation;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Callback;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\Walk;
use ValuePad\Core\Amc\Persistables\FeeByCountyPersistable;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Location\Services\CountyService;
use ValuePad\Core\Support\Service\ContainerInterface;

class SyncFeesByCountyValidator extends AbstractThrowableValidator
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var CountyService
     */
    private $countyService;

    /**
     * @param ContainerInterface $container
     * @param State $state
     */
    public function __construct(ContainerInterface $container, State $state)
    {
        $this->state = $state;
        $this->countyService = $container->get(CountyService::class);
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('data', function(Property $property){
            $property->addRule(new Walk(function(Binder $binder){

                $binder->bind('county', function(Property $property){
                    $property->addRule(new Obligate());
                });

                $binder->bind('amount', function(Property $property){
                    $property
                        ->addRule(new Obligate())
                        ->addRule(new Greater(0));
                });

            }));

            $property->addRule((new Callback([$this, 'uniqueCounties']))
                ->setIdentifier('unique')
                ->setMessage('The provided counties should be unique in the scope of the current collection.'));

            $property->addRule((new Callback([$this, 'existCounties']))
                ->setIdentifier('exists')
                ->setMessage('One of the provided counties does not exist within the provided state.'));
        });
    }

    /**
     * @param FeeByCountyPersistable[] $persistables
     * @return bool
     */
    public function uniqueCounties(array $persistables)
    {
        $values = [];

        foreach ($persistables as $persistable){
            $values[$persistable->getCounty()] = true;
        }

        return count($persistables) === count($values);
    }

    /**
     * @param FeeByCountyPersistable[] $persistables
     * @return bool
     */
    public function existCounties(array $persistables)
    {
        return $this->countyService->existSelectedInState(
            array_map(function(FeeByCountyPersistable $persistable){ return $persistable->getCounty(); }, $persistables),
            $this->state->getCode()
        );
    }
}
