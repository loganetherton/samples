<?php
namespace ValuePad\Core\Document\Support\Storage;

class FileDescriptor
{
    /**
     * @var int
     */
    private $size;

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }
}
