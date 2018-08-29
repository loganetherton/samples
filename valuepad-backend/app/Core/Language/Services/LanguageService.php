<?php
namespace ValuePad\Core\Language\Services;

use ValuePad\Core\Language\Entities\Language;
use ValuePad\Core\Support\Service\AbstractService;

class LanguageService extends AbstractService
{
    /**
     * @return Language[]
     */
    public function getAll()
    {
        return $this->entityManager->getRepository(Language::class)->findAll();
    }

    /**
     * @param string|string[] $codes
     * @return bool
     */
    public function exists($codes)
    {
        if (!is_array($codes)) {
            return $this->entityManager->getRepository(Language::class)->exists(['code' => $codes]);
        }

		$total = $this->entityManager->getRepository(Language::class)->count(['code' => ['in', $codes]]);

        return $total == count($codes);
    }
}
