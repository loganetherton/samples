<?php
namespace ValuePad\Core\Assignee\Support;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Assignee\Interfaces\CoverageInterface;
use ValuePad\Core\Assignee\Interfaces\CoveragePersistableInterface;
use ValuePad\Core\Assignee\Interfaces\CoverageStorableInterface;
use ValuePad\Core\Location\Entities\County;

class CoverageManagement
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $class;

    /**
     * @param EntityManagerInterface $entityManager
     * @param string $class;
     */
    public function __construct(EntityManagerInterface $entityManager, $class)
    {
        $this->entityManager = $entityManager;
        $this->class = $class;
    }

    /**
     * @param CoverageStorableInterface $license
     * @param CoveragePersistableInterface[] $persistables
     */
    public function addCoverages(CoverageStorableInterface $license, $persistables)
    {
        foreach ($persistables as $persistable) {

            /**
             * @var County $county
             */
            $county = $this->entityManager->getReference(County::class, $persistable->getCounty());

            if ($persistable->getZips()){
                foreach ($persistable->getZips() as $zip) {
                    $this->addCoverage($license, $county, $zip);

                }
            } else {
                $this->addCoverage($license, $county);
            }
        }
    }

    /**
     * @param CoverageStorableInterface $license
     */
    public function clearCoverages(CoverageStorableInterface $license)
    {
        foreach ($license->getCoverages() as $coverage){
            $this->entityManager->remove($coverage);
        }

        $license->clearCoverages();
    }


    /**
     * @param CoverageStorableInterface $license
     * @param County $county
     * @param string|null $zip
     * @return CoverageInterface
     */
    private function addCoverage(CoverageStorableInterface $license, County $county, $zip = null)
    {
        $class = $this->class;

        /**
         * @var CoverageInterface $coverage
         */
        $coverage = new $class();
        $coverage->setCounty($county);
        $coverage->setLicense($license);

        if ($zip){
            $coverage->setZip($zip);
        }

        $this->entityManager->persist($coverage);

        $license->addCoverage($coverage);

        return $coverage;
    }

    /**
     * @param CoverageInterface[] $coverages
     * @param string $class
     * @return CoveragePersistableInterface[]
     */
    public static function asPersistables($coverages, $class)
    {
        $hash = [];

        foreach ($coverages as $coverage){
            $county = $coverage->getCounty()->getId();
            if (!isset($hash[$county])){
                $hash[$county] = [];
            }

            if ($coverage->getZip()){
                $hash[$county][] = $coverage->getZip();
            }
        }

        $persistables = [];

        foreach ($hash as $county => $zips){
            /**
             * @var CoveragePersistableInterface $coverage
             */
            $coverage = new $class();
            $coverage->setCounty($county);
            $coverage->setZips($zips);
            $persistables[] = $coverage;
        }

        return $persistables;
    }
}
