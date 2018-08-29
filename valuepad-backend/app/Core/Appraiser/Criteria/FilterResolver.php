<?php
namespace ValuePad\Core\Appraiser\Criteria;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Asc\Enums\Certification;
use ValuePad\Core\Customer\Entities\JobType;
use ValuePad\Core\Support\Criteria\AbstractResolver;
use ValuePad\Core\Support\Criteria\Constraint;
use ValuePad\Core\Support\Criteria\Criteria;
use ValuePad\Core\Support\Criteria\Join;

class FilterResolver extends AbstractResolver
{
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var array
	 */
	private $qualifications = [
		'vaQualified', 'envCapable',
		'coopQualified', 'relocationQualified', 'usdaQualified',
		'jumboQualified', 'newConstructionQualified', 'loan203KQualified',
		'reoQualified', 'deskReviewQualified', 'fieldReviewQualified'
	];

	/**
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	/**
	 * @param Criteria $criteria
	 * @return bool
	 */
	public function canResolve(Criteria $criteria)
	{
		if (!$this->isQualificationsFilter($criteria)){
			return parent::canResolve($criteria);
		}

		return true;
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Criteria $criteria
	 * @return Join|null
	 */
	public function resolve(QueryBuilder $builder, Criteria $criteria)
	{
		if (!$this->isQualificationsFilter($criteria)){
			return parent::resolve($builder, $criteria);
		}

		return $this->resolveQualificationsFilter($builder, $criteria);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Criteria $criteria
	 * @return array
	 */
	private function resolveQualificationsFilter(QueryBuilder $builder, Criteria $criteria)
	{
		$field = $this->getQualificationField($criteria->getProperty());
		$placeholder = ':qualifications'.ucfirst($field);
		$flag = $criteria->getValue();

		$nullable = '';

		if ($flag === false){
			$nullable = ' OR q.'.$field.' IS NULL';
		}

		$builder->andWhere('('.'q.'.$field.' = '.$placeholder.$nullable.')')
			->setParameter($placeholder, $flag);

		return [new Join('a.qualifications', 'q')];
	}

	/**
	 * @param Criteria $criteria
	 * @return bool
	 */
	private function isQualificationsFilter(Criteria $criteria)
	{
		$field = $this->getQualificationField($criteria->getProperty());

		return in_array($field, $this->qualifications)
			&& $criteria->getConstraint()->is(Constraint::EQUAL)
			&& $criteria->getConstraint()->isNot() === false;
	}

	/**
	 * @param $property
	 * @return string
	 */
	private function getQualificationField($property)
	{
		$property = explode('.', $property);

		return array_pop($property);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $companyName
	 */
	public function whereCompanyNameSimilar(QueryBuilder $builder, $companyName)
	{
		$builder->andWhere($builder->expr()->like('a.companyName', ':companyName'))
			->setParameter('companyName', '%'.addcslashes($companyName, '%_').'%');
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $firstName
	 */
	public function whereFirstNameSimilar(QueryBuilder $builder, $firstName)
	{
		$builder->andWhere($builder->expr()->like('a.firstName', ':firstName'))
			->setParameter('firstName', '%'.addcslashes($firstName, '%_').'%');
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $lastName
	 */
	public function whereLastNameSimilar(QueryBuilder $builder, $lastName)
	{
		$builder->andWhere($builder->expr()->like('a.lastName', ':lastName'))
			->setParameter('lastName', '%'.addcslashes($lastName, '%_').'%');
	}

    /**
     * @param QueryBuilder $builder
     * @param string $fullName
     */
    public function whereFullNameSimilar(QueryBuilder $builder, $fullName)
    {
        $builder->andWhere($builder->expr()->like('a.fullName', ':fullName'))
            ->setParameter('fullName', '%'.addcslashes($fullName, '%_').'%');
    }

	/**
	 * @param QueryBuilder $builder
	 * @param string $zip
	 */
	public function whereZipSimilar(QueryBuilder $builder, $zip)
	{
		$builder->andWhere($builder->expr()->like('a.zip', ':zip'))
			->setParameter('zip', '%'.addcslashes($zip, '%_').'%');
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $state
	 */
	public function whereStateEqual(QueryBuilder $builder, $state)
	{
		$builder->andWhere($builder->expr()->eq('a.state', ':state'))
			->setParameter('state', $state);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $number
	 * @return Join[]
	 */
	public function whereQualificationsPrimaryLicenseNumberSimilar(QueryBuilder $builder, $number)
	{
		$builder->andWhere($builder->expr()->like('pl.number', ':primaryLicenseNumber'))
			->setParameter('primaryLicenseNumber', '%'.addcslashes($number, '%_').'%');

		return [new Join('a.qualifications', 'q'), new Join('q.primaryLicense', 'pl')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Certification[] $certifications
	 * @return Join[]
	 */
	public function whereQualificationsPrimaryLicenseCertificationsContain(QueryBuilder $builder, array $certifications) {

		$c = 0;
		$expression = '';
		$or = '';

		foreach ($certifications as $certification){
			$c ++;
			$placeholder = 'primaryLicenseCertification'.$c;
			$builder->setParameter($placeholder, '%"'.addcslashes($certification, '%_').'"%');
			$expression .= $or.'pl.certifications LIKE :'.$placeholder;
			$or = ' OR ';
		}

		$builder->andWhere('('.$expression.')');

		return [new Join('a.qualifications', 'q'), new Join('q.primaryLicense', 'pl')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param int $years
	 * @return Join[]
	 */
	public function whereQualificationsYearsLicensedEqual(QueryBuilder $builder, $years)
	{
		$builder->andWhere($builder->expr()->eq('q.yearsLicensed', ':yearsLicensed'))
			->setParameter('yearsLicensed', $years);

		return [new Join('a.qualifications', 'q')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param bool $flag
	 * @return Join[]
	 */
	public function whereQualificationsPrimaryLicenseIsFhaApprovedEqual(QueryBuilder $builder, $flag)
	{
		$builder->andWhere($builder->expr()->eq('pl.isFhaApproved', ':isFhaApproved'))
			->setParameter('isFhaApproved', $flag);

		return [new Join('a.qualifications', 'q'), new Join('q.primaryLicense', 'pl')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $state
	 * @return Join[]
	 */
	public function whereLicensesStateEqual(QueryBuilder $builder, $state)
	{
		$builder->andWhere($builder->expr()->eq('l.state', ':licenseState'))
			->setParameter('licenseState', $state);

		return [new Join('a.licenses', 'l')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param int $county
	 * @return Join[]
	 */
	public function whereLicensesCoveragesCountyEqual(QueryBuilder $builder, $county)
	{
		$builder->andWhere($builder->expr()->eq('c.county', ':licensedCoveragesCounty'))
			->setParameter('licensedCoveragesCounty', $county);

		return [new Join('a.licenses', 'l'), new Join('l.coverages', 'c')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param int $county
	 * @return Join[]
	 */
	public function whereLicensesCoveragesZipEqual(QueryBuilder $builder, $county)
	{
		$builder->andWhere($builder->expr()->eq('c.zip', ':licensesCoveragesZip'))
			->setParameter('licensesCoveragesZip', $county);

		return [new Join('a.licenses', 'l'), new Join('l.coverages', 'c')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param array $certifications
	 * @return Join[]
	 */
	public function whereLicensesCertificationsContain(QueryBuilder $builder, array $certifications)
	{
		$c = 0;
		$expression = '';
		$or = '';

		foreach ($certifications as $certification){
			$c ++;
			$placeholder = 'licensesCertification'.$c;
			$builder->setParameter($placeholder, '%"'.addcslashes($certification, '%_').'"%');
			$expression .= $or.'l.certifications LIKE :'.$placeholder;
			$or = ' OR ';
		}

		$builder->andWhere('('.$expression.')');

		return [new Join('a.licenses', 'l')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param array $jobTypeIds
	 * @return Join[]
	 */
	public function whereJobTypesContain(QueryBuilder $builder, array $jobTypeIds)
	{
		$customers = $jobTypeIds;
		$default = [];

		/**
		 * @var JobType[] $jobTypes
		 */
		$jobTypes = $this->entityManager->getRepository(JobType::class)
			->retrieveAll(['id' => ['in', $jobTypeIds]]);

		foreach ($jobTypes as $jobType){
			if ($local = $jobType->getLocal()){
				$default[] = $local->getId();
			}
		}

		$joins = [];

		if (!$customers && !$default){
			return null;
		}

		$expression = '';
		$d = '';

		if ($customers){
			$expression .= 'cf.jobType IN (:customerJobTypes)';
			$d = ' OR ';
			$builder->setParameter('customerJobTypes', $customers);
			$joins[] = new Join('a.customerFees', 'cf');
		}

		if ($default){
			$expression .= $d.'df.jobType IN (:defaultJobTypes)';
			$builder->setParameter('defaultJobTypes', $default);
			$joins[] = new Join('a.defaultFees', 'df');
		}

		$builder->andWhere('('.$expression.')');

		return $joins;
	}
}
