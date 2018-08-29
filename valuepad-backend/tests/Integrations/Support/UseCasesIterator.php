<?php
namespace ValuePad\Tests\Integrations\Support;

use Ascope\Libraries\Support\Iterators\SubIteratorIterator;
use ArrayIterator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use ValuePad\Tests\Integrations\Support\Data\Init;
use ValuePad\Tests\Integrations\Support\Data\Metadata;
use RuntimeException;

/**
 * @author Sergei Melnikov <me@rnr.name>
 */
class UseCasesIterator extends SubIteratorIterator
{
    /**
     * @var string
     */
    private $path;

	/**
	 * @var array
	 */
	private $init = [];

    public function __construct()
    {
        $this->path = realpath(__DIR__ . '/../UseCases');

        $finder = (new Finder())
            ->in($this->path)
            ->files()
            ->name('/^.+\.php$/i');

        parent::__construct($finder, function (SplFileInfo $file) {
            return new ArrayIterator(include $file->getPathname());
        });
    }

    /**
     * @return string
     */
    public function key()
    {
        $file = $this->getRelativePath();
        $request = $this->getSubIterator()->key();

        return "{$file}:{$request}";
    }

	public function next()
	{
		parent::next();
		$this->moveIfNeeded();
	}

	public function rewind()
	{
		parent::rewind();
		$this->moveIfNeeded();
	}

	private function moveIfNeeded()
	{
		if (!$this->valid()){
			return ;
		}

		$key = $this->getSubIterator()->key();

		if (ends_with($key, ':init')){

			if (isset($this->init[$key])){
				throw new RuntimeException('Seems the init-config has been added already under the "'.$key.'" key.');
			}

			$this->init[$key] = parent::current();
			$this->next();
		}
	}

	/**
     * @return string
     */
    private function getRelativePath()
    {
        return str_replace($this->path . '/', '', $this->getIterator()->key());
    }

    /**
     * @return array
     */
    public function current()
    {
        $metadata = new Metadata();

        $metadata
            ->setName($this->getSubIterator()->key())
            ->setData(parent::current())
            ->setPath($this->getRelativePath());

		$init = [];

		foreach ($this->init as $name =>  $data){
			$init[] = (new Init())->setData($data)->setName($name);
		}

		$this->init = [];

        return [$metadata, $init];
    }
}