<?php
return [
	'listeners' => [
		ValuePad\Push\Support\CustomerNotifier::class,
		ValuePad\Push\Support\AmcNotifier::class,
		ValuePad\Letter\Support\Notifier::class,
		ValuePad\Log\Support\Notifier::class,
		ValuePad\Live\Support\Notifier::class,
		ValuePad\Mobile\Support\Notifier::class
	],
	'push' => [
		'handlers' => [
			'customer' => [
                ValuePad\Core\Invitation\Notifications\AcceptInvitationNotification::class =>
                    ValuePad\Push\Handlers\Customer\Invitation\AcceptInvitationHandler::class,

                ValuePad\Core\Invitation\Notifications\DeclineInvitationNotification::class =>
                    ValuePad\Push\Handlers\Customer\Invitation\DeclineInvitationHandler::class,

                ValuePad\Core\Appraisal\Notifications\UpdateProcessStatusNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraisal\UpdateProcessStatusHandler::class,

                ValuePad\Core\Appraisal\Notifications\AcceptOrderWithConditionsNotification::class =>
                    \ValuePad\Push\Handlers\Customer\Appraisal\AcceptOrderWithConditionsHandler::class,

                ValuePad\Core\Appraisal\Notifications\CreateAdditionalDocumentNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraisal\CreateAdditionalDocumentHandler::class,

                ValuePad\Core\Appraisal\Notifications\CreateDocumentNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraisal\CreateDocumentHandler::class,

                ValuePad\Core\Appraisal\Notifications\DeclineOrderNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraisal\DeclineOrderHandler::class,

                ValuePad\Core\Appraisal\Notifications\SendMessageNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraisal\SendMessageHandler::class,

                ValuePad\Core\Appraisal\Notifications\SubmitBidNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraisal\SubmitBidHandler::class,

                ValuePad\Core\Appraisal\Notifications\UpdateDocumentNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraisal\UpdateDocumentHandler::class,

                ValuePad\Core\Appraisal\Notifications\ChangeAdditionalStatusNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraisal\ChangeAdditionalStatusHandler::class,

                ValuePad\Core\Appraisal\Notifications\PayTechFeeNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraisal\PayTechFeeHandler::class,

                ValuePad\Core\Appraiser\Notifications\UpdateAppraiserNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraiser\UpdateAppraiserHandler::class,

                ValuePad\Core\Appraiser\Notifications\CreateLicenseNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraiser\LicenseHandler::class,

                ValuePad\Core\Appraiser\Notifications\UpdateLicenseNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraiser\LicenseHandler::class,

                ValuePad\Core\Appraiser\Notifications\DeleteLicenseNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraiser\LicenseHandler::class,

                ValuePad\Core\Appraiser\Notifications\UpdateAchNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraiser\UpdateAchHandler::class,

                ValuePad\Core\Amc\Notifications\ChangeCustomerFeesNotification::class =>
                    ValuePad\Push\Handlers\Customer\Amc\ChangeCustomerFeesHandler::class,

                ValuePad\Core\Appraiser\Notifications\ChangeCustomerFeesNotification::class =>
                    ValuePad\Push\Handlers\Customer\Appraiser\ChangeCustomerFeesHandler::class,

                ValuePad\Core\Shared\Notifications\AvailabilityPerCustomerNotification::class =>
                    ValuePad\Push\Handlers\Customer\Shared\AvailabilityPerCustomerHandler::class

            ],
            'amc' => [
                ValuePad\Core\Appraisal\Notifications\AwardOrderNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\AwardOrderHandler::class,

                ValuePad\Core\Appraisal\Notifications\BidRequestNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\BidRequestHandler::class,

                ValuePad\Core\Appraisal\Notifications\ChangeAdditionalStatusNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\ChangeAdditionalStatusHandler::class,

                ValuePad\Core\Appraisal\Notifications\CreateAdditionalDocumentNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\CreateAdditionalDocumentHandler::class,

                ValuePad\Core\Appraisal\Notifications\CreateDocumentNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\CreateDocumentHandler::class,

                ValuePad\Core\Log\Notifications\CreateLogNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\CreateLogHandler::class,

                ValuePad\Core\Appraisal\Notifications\CreateOrderNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\CreateOrderHandler::class,

                ValuePad\Core\Appraisal\Notifications\DeleteAdditionalDocumentNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\DeleteAdditionalDocumentHandler::class,

                ValuePad\Core\Appraisal\Notifications\DeleteDocumentNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\DeleteDocumentHandler::class,

                ValuePad\Core\Appraisal\Notifications\DeleteOrderNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\DeleteOrderHandler::class,

                ValuePad\Core\Appraisal\Notifications\SendMessageNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\SendMessageHandler::class,

                ValuePad\Core\Appraisal\Notifications\UpdateDocumentNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\UpdateDocumentHandler::class,

                ValuePad\Core\Appraisal\Notifications\UpdateOrderNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\UpdateOrderHandler::class,

                ValuePad\Core\Appraisal\Notifications\UpdateProcessStatusNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\UpdateProcessStatusHandler::class,

                ValuePad\Core\Appraisal\Notifications\ReconsiderationRequestNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\ReconsiderationRequestHandler::class,

                ValuePad\Core\Appraisal\Notifications\RevisionRequestNotification::class =>
                    ValuePad\Push\Handlers\Amc\Appraisal\RevisionRequestHandler::class,
            ]
		]
	],
	'letter' => [
		'handlers' => [
			ValuePad\Core\Appraisal\Notifications\UpdateOrderNotification::class =>
				ValuePad\Letter\Handlers\Appraisal\UpdateOrderHandler::class,

			ValuePad\Core\Appraisal\Notifications\CreateOrderNotification::class =>
				ValuePad\Letter\Handlers\Appraisal\CreateOrderHandler::class,

			ValuePad\Core\Appraisal\Notifications\DeleteOrderNotification::class =>
				ValuePad\Letter\Handlers\Appraisal\DeleteOrderHandler::class,

			ValuePad\Core\Appraisal\Notifications\UpdateProcessStatusNotification::class =>
				ValuePad\Letter\Handlers\Appraisal\UpdateProcessStatusHandler::class,

			ValuePad\Core\Appraisal\Notifications\BidRequestNotification::class =>
				ValuePad\Letter\Handlers\Appraisal\BidRequestHandler::class,

			ValuePad\Core\Appraisal\Notifications\ChangeAdditionalStatusNotification::class =>
				ValuePad\Letter\Handlers\Appraisal\ChangeAdditionalStatusHandler::class,

			ValuePad\Core\Appraisal\Notifications\SendMessageNotification::class =>
				ValuePad\Letter\Handlers\Appraisal\SendMessageHandler::class,

			ValuePad\Core\Appraisal\Notifications\CreateDocumentNotification::class =>
				ValuePad\Letter\Handlers\Appraisal\CreateDocumentHandler::class,

			ValuePad\Core\Appraisal\Notifications\DeleteDocumentNotification::class =>
				ValuePad\Letter\Handlers\Appraisal\DeleteDocumentHandler::class,

			ValuePad\Core\Appraisal\Notifications\CreateAdditionalDocumentNotification::class =>
				ValuePad\Letter\Handlers\Appraisal\CreateAdditionalDocumentHandler::class,

			ValuePad\Core\Appraisal\Notifications\DeleteAdditionalDocumentNotification::class =>
				ValuePad\Letter\Handlers\Appraisal\DeleteAdditionalDocumentHandler::class,

			ValuePad\Core\Appraisal\Notifications\RevisionRequestNotification::class =>
				ValuePad\Letter\Handlers\Appraisal\RevisionRequestHandler::class,

			ValuePad\Core\Appraisal\Notifications\ReconsiderationRequestNotification::class =>
				ValuePad\Letter\Handlers\Appraisal\ReconsiderationRequestHandler::class,

			ValuePad\Core\Amc\Notifications\CreateAmcNotification::class =>
				ValuePad\Letter\Handlers\Amc\CreateAmcHandler::class,

			ValuePad\Core\Amc\Notifications\ApproveAmcNotification::class =>
				ValuePad\Letter\Handlers\Amc\ApproveAmcHandler::class,

			ValuePad\Core\Amc\Notifications\DeclineAmcNotification::class =>
				ValuePad\Letter\Handlers\Amc\DeclineAmcHandler::class,

            ValuePad\Core\Appraisal\Notifications\AwardOrderNotification::class =>
                ValuePad\Letter\Handlers\Appraisal\AwardOrderHandler::class,

            ValuePad\Core\Company\Notifications\CreateStaffNotification::class =>
                ValuePad\Letter\Handlers\Company\CreateStaffHandler::class,

            ValuePad\Core\Company\Notifications\CreateCompanyInvitationNotification::class =>
                ValuePad\Letter\Handlers\Company\CreateCompanyInvitationHandler::class,

            ValuePad\Core\Appraisal\Notifications\AcceptOrderWithConditionsNotification::class =>
                ValuePad\Letter\Handlers\Appraisal\AcceptOrderWithConditionsHandler::class,

            ValuePad\Core\Appraisal\Notifications\DeclineOrderNotification::class =>
                ValuePad\Letter\Handlers\Appraisal\DeclineOrderHandler::class,

            ValuePad\Core\Appraisal\Notifications\SubmitBidNotification::class =>
                ValuePad\Letter\Handlers\Appraisal\SubmitBidHandler::class
		]
	],
	'live' => [
		'handlers' => [
			ValuePad\Core\Log\Notifications\CreateLogNotification::class =>
				ValuePad\Live\Handlers\CreateLogHandler::class,

			ValuePad\Core\Appraisal\Notifications\SendMessageNotification::class =>
				ValuePad\Live\Handlers\SendMessageHandler::class,

			ValuePad\Core\Appraisal\Notifications\CreateOrderNotification::class =>
				ValuePad\Live\Handlers\CreateOrderHandler::class,

			ValuePad\Core\Appraisal\Notifications\UpdateOrderNotification::class =>
				ValuePad\Live\Handlers\UpdateOrderHandler::class,

			ValuePad\Core\Appraisal\Notifications\UpdateProcessStatusNotification::class =>
				ValuePad\Live\Handlers\UpdateProcessStatusHandler::class,

			ValuePad\Core\Appraisal\Notifications\RevisionRequestNotification::class =>
				ValuePad\Live\Handlers\UpdateProcessStatusHandler::class,

			ValuePad\Core\Appraisal\Notifications\ReconsiderationRequestNotification::class =>
				ValuePad\Live\Handlers\UpdateProcessStatusHandler::class,

			ValuePad\Core\Appraisal\Notifications\DeleteOrderNotification::class =>
				ValuePad\Live\Handlers\DeleteOrderHandler::class,

			ValuePad\Core\Appraisal\Notifications\BidRequestNotification::class =>
				ValuePad\Live\Handlers\BidRequestHandler::class,

			ValuePad\Core\Appraisal\Notifications\ChangeAdditionalStatusNotification::class =>
				ValuePad\Live\Handlers\ChangeAdditionalStatusHandler::class,

			ValuePad\Core\Appraisal\Notifications\AcceptOrderWithConditionsNotification::class =>
				ValuePad\Live\Handlers\AcceptOrderWithConditionsHandler::class,

			ValuePad\Core\Appraisal\Notifications\DeclineOrderNotification::class =>
				ValuePad\Live\Handlers\DeclineOrderHandler::class,

            ValuePad\Core\Appraisal\Notifications\CreateDocumentNotification::class =>
                ValuePad\Live\Handlers\CreateOrderDocumentHandler::class,

            ValuePad\Core\Appraisal\Notifications\UpdateDocumentNotification::class =>
                ValuePad\Live\Handlers\UpdateOrderDocumentHandler::class,

            ValuePad\Core\Appraisal\Notifications\DeleteDocumentNotification::class =>
                ValuePad\Live\Handlers\DeleteOrderDocumentHandler::class,

            ValuePad\Core\Appraisal\Notifications\CreateAdditionalDocumentNotification::class =>
                ValuePad\Live\Handlers\CreateOrderAdditionalDocumentHandler::class,

            ValuePad\Core\Appraisal\Notifications\DeleteAdditionalDocumentNotification::class =>
                ValuePad\Live\Handlers\DeleteOrderAdditionalDocumentHandler::class,

            ValuePad\Core\Appraisal\Notifications\AwardOrderNotification::class =>
                ValuePad\Live\Handlers\AwardOrderHandler::class,

            ValuePad\Core\Appraisal\Notifications\SubmitBidNotification::class =>
                ValuePad\Live\Handlers\SubmitBidHandler::class

		]
	],
	'mobile' => [
		'handlers' => [
			ValuePad\Core\Log\Notifications\CreateLogNotification::class =>
				ValuePad\Mobile\Handlers\CreateLogHandler::class,

			ValuePad\Core\Appraisal\Notifications\DeleteOrderNotification::class =>
				ValuePad\Mobile\Handlers\DeleteOrderHandler::class,

			ValuePad\Core\Appraisal\Notifications\SendMessageNotification::class =>
				ValuePad\Mobile\Handlers\SendMessageHandler::class,

            ValuePad\Core\Appraisal\Notifications\AcceptOrderWithConditionsNotification::class =>
                ValuePad\Mobile\Handlers\AcceptOrderWithConditionsHandler::class,

            ValuePad\Core\Appraisal\Notifications\DeclineOrderNotification::class =>
                ValuePad\Mobile\Handlers\DeclineOrderHandler::class,

            ValuePad\Core\Appraisal\Notifications\SubmitBidNotification::class =>
                ValuePad\Mobile\Handlers\SubmitBidHandler::class
		]
	]
];
