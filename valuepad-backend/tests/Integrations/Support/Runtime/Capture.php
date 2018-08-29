<?php
namespace ValuePad\Tests\Integrations\Support\Runtime;

class Capture
{
    /**
     * @var array
     */
    private static $data;

    /**
     * @param $name
     * @param array $data
     */
    public static function add($name, array $data)
    {
        static::$data[$name] = $data;
    }

    /**
     * @return void
     */
    public static function reset()
    {
        static::$data = [];
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function get($path)
    {
        $path = explode('.', $path);

        $name = array_shift($path);

        if (!$path){
            return self::$data[$name];
        }

        return array_get(self::$data[$name], implode('.', $path));
    }
}
