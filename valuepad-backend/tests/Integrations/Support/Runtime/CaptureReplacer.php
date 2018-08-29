<?php
namespace ValuePad\Tests\Integrations\Support\Runtime;

/**
 * @author Sergei Melnikov <me@rnr.name>
 */
class CaptureReplacer
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var Capture
     */
    private $capture;

    /**
     * CaptureReplacer constructor.
     * @param string $value
     */
    public function __construct($value) {
        $this->value = $value;
        $this->capture = new Capture();
    }

    /**
     * @return string
     */
    function __toString()
    {
        $value = $this->value;

        if (preg_match_all('/{:(.+?)}/', $this->value, $matches)) {
            foreach ($matches[0] as $i => $match) {
                $value = str_replace($match, $this->capture->get($matches[1][$i]), $value);
            };
        }

        return $value;
    }
}