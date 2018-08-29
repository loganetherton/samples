<?php
namespace ValuePad\Core\Appraiser\Validation;

use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Ascope\Libraries\Validation\SourceHandlerInterface;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Enums\CommercialExpertise;
use ValuePad\Core\Appraiser\Enums\CommercialExpertiseCollection;
use ValuePad\Core\Asc\Enums\Certification;
use ValuePad\Core\Asc\Enums\Certifications;
use ValuePad\Core\Support\Validation\AbstractConstraints;

class Constraints extends AbstractConstraints
{
	const PRIMARY_LICENSE_CERTIFICATIONS_CONTAIN_RESIDENTIAL_OR_GENERAL
		= 'primaryLicenseCertificationsContainResidentialOrGeneral';

	const PRIMARY_LICENSE_CERTIFICATIONS_CONTAIN_GENERAL = 'primaryLicenseCertificationsContainGeneral';
	const COMMERCIAL_QUALIFIED_EQUALS_TRUE = 'commercialQualifiedEqualsTrue';
	const COMMERCIAL_EXPERTISE_CONTAIN_OTHER = 'commercialExpertiseContainOther';
	const QUESTION_1_EQUALS_TRUE = 'question1EqualsTrue';

	/**
	 * @var Appraiser
	 */
	private $appraiser;

	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @return bool
	 */
	public function primaryLicenseCertificationsContainResidentialOrGeneral(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors
	)
	{
		$certifications = $this->tryGetCertifications($source, $errors);

		if (!$certifications){

			return false;
		}

		return $certifications->has(new Certification(Certification::CERTIFIED_GENERAL))
			|| $certifications->has(new Certification(Certification::CERTIFIED_RESIDENTIAL));
	}

	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @return bool
	 */
	public function primaryLicenseCertificationsContainGeneral(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors
	)
	{
		$certifications = $this->tryGetCertifications($source, $errors);

		if (!$certifications){
			return false;
		}

		return $certifications->has(new Certification(Certification::CERTIFIED_GENERAL));
	}

	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @return bool
	 */
	public function commercialQualifiedEqualsTrue(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors
	)
	{
		if (isset($errors['qualifications.commercialQualified'])){
			return false;
		}

		return $source->getValue('qualifications.commercialQualified') === true;
	}


	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @return bool
	 */
	public function commercialExpertiseContainOther(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors
	)
	{
		if (isset($errors['qualifications.commercialExpertise'])){
			return false;
		}

		/**
		 * @var CommercialExpertiseCollection $collection
		 */
        $collection = $source->getValue('qualifications.commercialExpertise');

		if ($collection === null){
			return false;
		}

		return $collection->has(new CommercialExpertise(CommercialExpertise::OTHER));
	}

	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @param $index
	 * @return bool
	 */
	public function questionEqualsTrue(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors,
		$index
	)
	{
		if (isset($errors['eo.question'.$index])){
			return false;
		}

		return $source->getValue('eo.question'.$index) === true;
	}

	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @param int $index
	 * @return bool
	 */
	public function questionNotSpecified(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors,
		$index
	)
	{
		if (isset($errors['eo.question'.$index])){
			return false;
		}

		return !$source->hasProperty('eo.question'.$index);
	}

	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @param int $index
	 * @return bool
	 */
	public function questionFromRepositoryEqualsTrue(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors,
		$index
	)
	{
		return object_take($this->appraiser, 'eo.question'.$index) === true;
	}

	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @param int $index
	 * @return bool
	 */
	public function questionExplanationNotSpecified(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors,
		$index
	)
	{
		if (isset($errors['eo.question'.$index.'Explanation'])){
			return false;
		}

		return !$source->hasProperty('eo.question'.$index.'Explanation');
	}

	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @return bool
	 */
	public function question1EqualsTrue(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors
	)
	{
		return $this->questionEqualsTrue($source, $errors, 1);
	}

	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @return bool
	 */
	public function newConstructionQualifiedEqualsTrue(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors
	)
	{
		if (isset($errors['qualifications.newConstructionQualified'])){
			return false;
		}

		return $source->getValue('qualifications.newConstructionQualified') === true;
	}

	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @return bool
	 */
	public function newConstructionQualifiedNotSpecified(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors
	)
	{
		if (isset($errors['qualifications.newConstructionQualified'])){
			return false;
		}

		return !$source->hasProperty('qualifications.newConstructionQualified');
	}

	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @return bool
	 */
	public function newConstructionQualifiedFromRepositoryEqualsTrue(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors
	)
	{
		return  object_take($this->appraiser, 'qualifications.newConstructionQualified') === true;
	}

	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @param $field
	 * @return bool
	 */
	public function fieldNotSpecified(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors,
		$field
	)
	{
		if (isset($errors[$field])){
			return false;
		}

		return !$source->hasProperty($field);
	}

	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @return bool
	 */
	private function isCertificationProcessable(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors
	)
	{
		return !isset($errors['qualifications.primaryLicense.certifications']);
	}


	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @return Certifications
	 */
	private function tryGetCertifications(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors
	)
	{
		if (!$this->isCertificationProcessable($source, $errors)){
			return null;
		}

		return $source->getValue('qualifications.primaryLicense.certifications');
	}

	/**
	 * @param Appraiser $appraiser
	 */
	public function setAppraiser(Appraiser $appraiser)
	{
		$this->appraiser = $appraiser;
	}
}
