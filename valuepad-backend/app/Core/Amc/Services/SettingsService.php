<?php
namespace ValuePad\Core\Amc\Services;
use ValuePad\Core\Amc\Entities\Settings;
use ValuePad\Core\Amc\Persistables\SettingsPersistable;
use ValuePad\Core\Amc\Validation\SettingsValidator;
use ValuePad\Core\Shared\Options\UpdateOptions;
use ValuePad\Core\Support\Service\AbstractService;

class SettingsService extends AbstractService
{
    /**
     * @param int $amcId
     * @return Settings
     */
    public function get($amcId)
    {
        return $this->entityManager->getRepository(Settings::class)
            ->findOneBy(['amc' => $amcId]);
    }

    /**
     * @param int $amcId
     * @param SettingsPersistable $persistable
     * @param UpdateOptions $options
     */
    public function update($amcId, SettingsPersistable $persistable, UpdateOptions $options)
    {
        if ($options === null){
            $options = new UpdateOptions();
        }

        (new SettingsValidator())
            ->setForcedProperties($options->getPropertiesScheduledToClear())
            ->validate($persistable, true);

        /**
         * @var Settings $settings
         */
        $settings = $this->entityManager->getRepository(Settings::class)
            ->findOneBy(['amc' => $amcId]);

        $this->transfer($persistable, $settings, [
            'nullable' => $options->getPropertiesScheduledToClear()
        ]);

        $this->entityManager->flush();
    }
}
