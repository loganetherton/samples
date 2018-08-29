<?php
namespace ValuePad\Core\Appraiser\Validation\Definers;

use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Numeric;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\SourceHandlerInterface;

class AchDefiner
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

        foreach (['bankName', 'accountNumber', 'accountType', 'routing'] as $field){
            $bundle = $binder->bind($namespace.$field, function(Property $property){
                $property
                    ->addRule(new Obligate());
            });

            if ($path){
                $bundle->when(function(SourceHandlerInterface $source) use ($path){
                    return $source->hasProperty($path);
                });
            }
        }

        $binder->bind($namespace.'bankName', function(Property $property){
            $property
                ->addRule(new Blank());
        });

        $binder->bind($namespace.'accountNumber', function(Property $property){
            $property
                ->addRule(new Blank())
                ->addRule(new Numeric())
                ->addRule(new Length(1, 20));
        });

        $binder->bind($namespace.'routing', function(Property $property){
            $property
                ->addRule(new Blank())
                ->addRule(new Numeric())
                ->addRule(new Length(9, 9));
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
