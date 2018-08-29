<?php
namespace ValuePad\Core\Document\Persistables;

class DocumentPersistable
{
    /**
     * @var string|resource
     */
    private $location;

    /**
     * @var string
     */
    private $suggestedName;

    /**
     * @param string $name
     */
    public function setSuggestedName($name)
    {
        $this->suggestedName = $name;
	}

    /**
     * @return string
     */
    public function getSuggestedName()
    {
        return $this->suggestedName;
    }

    /**
     * @param string|resource $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
	}

    /**
     * @return string|resource
     */
    public function getLocation()
    {
        return $this->location;
    }
}
