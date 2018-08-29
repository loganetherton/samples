<?php
namespace ValuePad\DAL\Appraisal\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Amc\Entities\Item;
use ValuePad\Core\Appraisal\Entities\AcceptedConditions;
use ValuePad\Core\Appraisal\Entities\AdditionalDocument;
use ValuePad\Core\Appraisal\Entities\AdditionalExternalDocument;
use ValuePad\Core\Appraisal\Entities\Bid;
use ValuePad\Core\Appraisal\Entities\Fdic;
use ValuePad\Core\Appraisal\Entities\InstructionExternalDocument;
use ValuePad\Core\Appraisal\Entities\Property;
use ValuePad\Core\Appraisal\Entities\SupportingDetails;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\Core\Customer\Entities\AdditionalStatus;
use ValuePad\Core\Customer\Entities\Client;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Entities\JobType;
use ValuePad\Core\Customer\Entities\Ruleset;
use ValuePad\Core\Invitation\Entities\Invitation;
use ValuePad\Core\User\Entities\User;
use ValuePad\DAL\Appraisal\Types\ApproachesToBeIncludedType;
use ValuePad\DAL\Appraisal\Types\ConcessionUnitType;
use ValuePad\DAL\Appraisal\Types\OrderValueQualifiers;
use ValuePad\DAL\Appraisal\Types\ProcessStatusType;
use ValuePad\DAL\Appraisal\Types\WorkflowType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class OrderMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('orders');

		$this->defineId($builder);

		$builder
			->createField('fileNumber', 'string')
			->length(static::FILE_NUMBER_LENGTH)
			->build();

        $builder
            ->createField('intendedUse', 'string')
            ->nullable(true)
            ->build();

		$builder
			->createField('referenceNumber', 'string')
			->nullable(true)
			->build();

		$builder
			->createManyToOne('client', Client::class)
			->build();

		$builder
			->createManyToOne('clientDisplayedOnReport', Client::class)
			->build();

		$builder
			->createField('amcLicenseNumber', 'string')
			->nullable(true)
			->length(100)
			->build();

		$builder
			->createField('amcLicenseExpiresAt', 'datetime')
			->nullable(true)
			->build();

		$builder
			->createField('fee', 'float')
			->nullable(true)
			->build();

		$builder
			->createField('techFee', 'float')
			->nullable(true)
			->build();

		$builder
			->createField('purchasePrice', 'float')
			->nullable(true)
			->build();

		$builder
			->createField('fhaNumber', 'string')
			->nullable(true)
			->length(100)
			->build();

		$builder
			->createField('loanNumber', 'string')
			->nullable(true)
			->length(static::LOAN_NUMBER_LENGTH)
			->build();

		$builder
			->createField('loanType', 'string')
			->nullable(true)
			->build();

		$builder
			->createField('loanAmount', 'float')
			->nullable(true)
			->build();

		$builder
			->createManyToOne('contractDocument', AdditionalDocument::class)
			->build();

		$builder
			->createField('contractDate', 'datetime')
			->nullable(true)
			->build();

		$builder
			->createField('salesPrice', 'float')
			->nullable(true)
			->build();

		$builder
			->createField('concession', 'float')
			->nullable(true)
			->build();

		$builder
			->createField('concessionUnit', ConcessionUnitType::class)
			->nullable(true)
			->build();

		$builder
			->createField('processStatus', ProcessStatusType::class)
			->build();

		$builder
			->createField('approachesToBeIncluded', ApproachesToBeIncludedType::class)
			->build();

		$builder
			->createField('dueDate', 'datetime')
			->nullable(true)
			->build();

		$builder
			->createField('orderedAt', 'datetime')
			->build();

		$builder
			->createField('instruction', 'text')
			->nullable(true)
			->build();

		$builder
			->createManyToOne('jobType', JobType::class)
			->build();

		$builder
			->createManyToMany('additionalJobTypes', JobType::class)
			->setJoinTable('order_additional_job_types')
			->build();

		$builder
			->createOneToMany('instructionDocuments', InstructionExternalDocument::class)
			->cascadeRemove()
			->mappedBy('order')
			->build();

		$builder
			->createOneToMany('additionalDocuments', AdditionalExternalDocument::class)
			->cascadeRemove()
			->mappedBy('order')
			->build();

		$builder
			->createField('inspectionScheduledAt', 'datetime')
			->nullable(true)
			->build();

		$builder
			->createField('inspectionCompletedAt', 'datetime')
			->nullable(true)
			->build();

		$builder
			->createField('estimatedCompletionDate', 'datetime')
			->nullable(true)
			->build();

		$builder
			->createField('completedAt', 'datetime')
			->nullable(true)
			->build();

		$builder
			->createField('assignedAt', 'datetime')
			->nullable(true)
			->build();

		$builder
			->createField('acceptedAt', 'datetime')
			->nullable(true)
			->build();

		$builder
			->createField('paidAt', 'datetime')
			->nullable(true)
			->build();

        $builder
            ->createOneToOne('fdic', Fdic::class)
            ->cascadeRemove()
            ->build();

		$builder
			->createOneToOne('property', Property::class)
			->cascadeRemove()
			->build();

		$builder
			->createManyToOne('assignee', User::class)
			->build();

        $builder
            ->createManyToOne('staff', Staff::class)
            ->build();

		$builder
			->createManyToOne('customer', Customer::class)
			->build();

		$builder
			->createOneToMany('bid', Bid::class)
			->mappedBy('order')
			->cascadeRemove()
			->build();

		$builder
			->createField('workflow', WorkflowType::class)
			->build();

		$builder
			->createField('comment', 'text')
			->nullable(true)
			->build();

        $builder
            ->createField('isRush', 'boolean')
            ->build();

		$builder
			->createField('isPaid', 'boolean')
			->build();

		$builder
			->createField('putOnHoldAt', 'datetime')
			->nullable(true)
			->build();

		$builder
			->createField('revisionReceivedAt', 'datetime')
			->nullable(true)
			->build();

		$builder
			->createField('isTechFeePaid', 'boolean')
			->build();

		$builder
			->createManyToOne('additionalStatus', AdditionalStatus::class)
			->build();

		$builder
			->createField('additionalStatusComment', 'text')
			->nullable(true)
			->build();

		$builder
			->createManyToOne('invitation', Invitation::class)
			->build();

		$builder
			->createOneToOne('acceptedConditions', AcceptedConditions::class)
			->cascadeRemove()
			->build();

		$builder->createManyToMany('rulesets', Ruleset::class)
			->setJoinTable('orders_rulesets')
			->build();


        $builder->createField('lienPosition', 'string')
            ->nullable(true)
            ->build();

        $builder
            ->createField('valueQualifiers', OrderValueQualifiers::class)
            ->build();

		$builder->createField('createdAt', 'datetime')
			->nullable(true)
			->build();

		$builder->createField('updatedAt', 'datetime')
			->nullable(true)
			->build();

        $builder
            ->createOneToOne('supportingDetails', SupportingDetails::class)
            ->mappedBy('order')
            ->cascadeRemove()
            ->build();

        $builder
        	->createOneToOne('invoiceItem', Item::class)
        	->mappedBy('order')
        	->build();

    	$builder
			->createField('tinAtCompletion', 'string')
			->nullable(true)
			->columnName('tinAtCompletion')
			->build();

        $builder
        	->createManyToMany('subAssignees', Appraiser::class)
        	->setJoinTable('orders_subassignees')
        	->build();
	}
}
