<?php
namespace ValuePad\Live\Support;

class Event
{
	/**
	 * @var Channel[]
	 */
	private $channels;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @return Channel[]
	 */
	public function getChannels()
	{
		return $this->channels;
	}

	/**
	 * @param Channel[] $channels
	 */
	public function setChannels(array $channels)
	{
		$this->channels = $channels;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param array $data
	 */
	public function setData($data)
	{
		$this->data = $data;
	}

    /**
     * @return string
     */
	public function __toString()
    {
        return $this->type.':'.$this->name;
    }
}
