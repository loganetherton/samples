<?php
namespace ValuePad\Letter\Handlers\Company;

use Illuminate\Config\Repository as Config;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use ValuePad\Core\Company\Notifications\CreateCompanyInvitationNotification;
use ValuePad\Letter\Support\HandlerInterface;

class CreateCompanyInvitationHandler implements HandlerInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param Mailer $mailer
     * @param CreateCompanyInvitationNotification $source
     */
    public function handle(Mailer $mailer, $source)
    {
        $invitation = $source->getInvitation();
        $company = $invitation->getBranch()->getCompany();

        $mailer->queue('emails.company.invite_appraiser', [
            'firstName' => $invitation->getAscAppraiser()->getFirstName(),
            'companyName' => $company->getName(),
            'loginUrl' => $this->config->get('app.front_end_url').'/login',
            'signUpUrl' => $this->config->get('app.front_end_url').'/appraiser-sign-up',
        ], function (Message $message) use ($invitation, $company) {
            $message->from($company->getEmail(), $company->getName());
            $message->subject('You\'ve Been Invited to Join '.$company->getName());
            $message->to($invitation->getEmail());
        });
    }
}