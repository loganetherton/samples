<?php
namespace ValuePad\Core\Shared\Validation\Definers;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\SourceHandlerInterface;
use ValuePad\Core\Shared\Validation\Rules\AvailabilityRange;

class AvailabilityDefiner
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @param Binder $binder
     */
    public function define(Binder $binder)
    {
        if ($namespace = $path = $this->namespace){
            $namespace .= '.';
        }

        $bundle = $binder->bind($namespace.'isOnVacation', function(Property $property){
            $property->addRule(new Obligate());
        });

        if ($path){
            $bundle->when(function(SourceHandlerInterface $source) use ($path){
                return $source->hasProperty($path);
            });
        }

        foreach (['from', 'to'] as $edge){
            $binder->bind($namespace.$edge, function(Property $property){
                $property->addRule(new Obligate());
            })
                ->when(function(SourceHandlerInterface $s) use ($namespace, $path){
                    return $s->getValue($namespace.'isOnVacation') === true;
                });
        }

        $binder->bind($namespace.'from', [$namespace.'from', $namespace.'to'], function(Property $property){
            $property
                ->addRule(new AvailabilityRange());
        });

        $binder->bind($namespace.'message', function(Property $property){
            $property->addRule(new Length(0, 1000));
        });
    }

    /**
     * @param string $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }
}
