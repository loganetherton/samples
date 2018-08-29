<?php
namespace ValuePad\Core\Amc\Validation;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Callback;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\Walk;
use ValuePad\Core\Amc\Persistables\FeeByZipPersistable;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Location\Services\ZipService;
use ValuePad\Core\Support\Service\ContainerInterface;

class SyncFeesByZipValidator extends AbstractThrowableValidator
{
    /**
     * @var State $state
     */
    private $state;

    /**
     * @var ZipService
     */
    private $zipService;

    /**
     * @param ContainerInterface $container
     * @param State $state
     */
    public function __construct(ContainerInterface $container, State $state)
    {
        $this->zipService = $container->get(ZipService::class);
        $this->state = $state;
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('data', function(Property $property){
            $property->addRule(new Walk(function(Binder $binder){

                $binder->bind('zip', function(Property $property){
                    $property->addRule(new Obligate());
                });

                $binder->bind('amount', function(Property $property){
                    $property
                        ->addRule(new Obligate())
                        ->addRule(new Greater(0));
                });

            }));

            $property->addRule((new Callback([$this, 'uniqueZips']))
                ->setIdentifier('unique')
                ->setMessage('The provided zip codes should be unique in the scope of the current collection.'));

            $property->addRule((new Callback([$this, 'existZips']))
                ->setIdentifier('exists')
                ->setMessage('One of the provided zip code does not exist within the provided state.'));
        });
    }

    /**
     * @param FeeByZipPersistable[] $persistables
     * @return bool
     */
    public function uniqueZips(array $persistables)
    {
        $values = [];

        foreach ($persistables as $persistable){
            $values[$persistable->getZip()] = true;
        }

        return count($persistables) === count($values);
    }

    /**
     * @param FeeByZipPersistable[] $persistables
     * @return bool
     */
    public function existZips(array $persistables)
    {
        return $this->zipService->existSelectedInState(
            array_map(function(FeeByZipPersistable $persistable){ return $persistable->getZip(); }, $persistables),
            $this->state->getCode()
        );
    }
}
