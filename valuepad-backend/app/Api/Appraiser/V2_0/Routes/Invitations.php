<?php
namespace ValuePad\Api\Appraiser\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Appraiser\V2_0\Controllers\InvitationsController;

class Invitations implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->get(
			'appraisers/{appraiserId}/invitations',
			InvitationsController::class.'@index'
		);

		$registrar->get(
			'appraisers/{appraiserId}/invitations/{invitationId}',
			InvitationsController::class.'@show'
		);

		$registrar->post(
			'appraisers/{appraiserId}/invitations/{invitationId}/accept',
			InvitationsController::class.'@accept'
		);

		$registrar->post(
			'appraisers/{appraiserId}/invitations/{invitationId}/decline',
			InvitationsController::class.'@decline'
		);
	}
}
