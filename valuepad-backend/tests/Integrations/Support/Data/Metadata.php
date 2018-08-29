<?php
namespace ValuePad\Tests\Integrations\Support\Data;

class Metadata extends DataProvider
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private static $lastPath;

    /**
     * @var string
     */
    private $previousPath;

    /**
     * @var bool
     */
    private $isAnotherFile;

    /**
     * @param string $path
     * @return Metadata
     */
    public function setPath($path)
    {
        if (self::$lastPath !== $path){
            $this->isAnotherFile = true;
            $this->previousPath = self::$lastPath;
        } else {
            $this->isAnotherFile = false;
        }

        $this->path = $path;
        self::$lastPath = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function isAnotherFile()
    {
        return $this->isAnotherFile;
    }

    /**
     * @return string
     */
    public function getPreviousPath()
    {
        return $this->previousPath;
    }
}
