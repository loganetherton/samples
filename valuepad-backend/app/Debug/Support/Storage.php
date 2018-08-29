<?php
namespace ValuePad\Debug\Support;

class Storage
{
	/**
	 * @var string
	 */
	private $file;

	/**
	 * @param string $file
	 */
	public function __construct($file)
	{
		$this->file = $file;
	}

	/**
	 * @return array
	 */
	public function dump()
	{
		$file = storage_path('debug/'.$this->file);

		if (!file_exists($file)){
			return [];
		}

		return json_decode(file_get_contents($file), true);
	}

	/**
	 * @return int
	 */
	public function size()
	{
		return count($this->dump());
	}

	/**
	 * @param array $data
	 */
	public function store(array $data)
	{
		$source = $this->dump();
		$source[] = $data;
		$this->restore($source);
	}

	/**
	 * @param array $data
	 */
	public function restore(array $data = [])
	{
		$dir = storage_path('debug');
		$file = $dir.'/'.$this->file;

		if (!file_exists($dir)){
			mkdir($dir, 0755, true);
		}

		file_put_contents($file, json_encode($data));
	}

	/**
	 * @param callable $filter
	 * @return array|null
	 */
	public function search(callable $filter)
	{
		$data = $this->dump();

		foreach ($data as $row){
			if ($filter($row) === true){
				return $row;
			}
		}

		return null;
	}

	/**
	 * @param callable $modifier
	 * @param callable $filter
	 */
	public function replace(callable $modifier, callable $filter)
	{
		$data = $this->dump();

		foreach ($data as $index => $row){
			if ($filter($row) === true){
				$data[$index] = $modifier($row);
			}
		}

		$this->restore($data);
	}

	public function drop()
	{
		$file = storage_path('debug/'.$this->file);

		if (file_exists($file)){
			unlink($file);
		}
	}
}
