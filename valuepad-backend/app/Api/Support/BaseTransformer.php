<?php
namespace ValuePad\Api\Support;

use Ascope\Libraries\Converter\Extractor\Extractor;
use Ascope\Libraries\Transformer\AbstractTransformer;
use ValuePad\Api\Support\Converter\Extractor\ExtraResolver;

/**
 *
 *
 */
abstract class BaseTransformer extends AbstractTransformer
{
    /**
     * @param mixed $value
     * @param string|array $modifier
     * @return mixed
     */
    public function modify($value, $modifier)
    {
        return $this->getModifierManager()->modify($value, $modifier);
    }

	/**
	 * @param array $options
	 * @return Extractor
	 */
	protected function createExtractor(array $options = [])
	{
		$extractor = parent::createExtractor($options);

		$extraResolver = new ExtraResolver();

		$extraResolver->setModifier($this->getModifierManager());

		$extractor->addGlobalResolver($extraResolver);

		return $extractor;
	}


}
