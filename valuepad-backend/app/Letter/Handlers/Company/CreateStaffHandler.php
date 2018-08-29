<?php
namespace ValuePad\Letter\Handlers\Company;

use Illuminate\Config\Repository as Config;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use ValuePad\Core\Company\Entities\Manager;
use ValuePad\Core\Company\Notifications\CreateStaffNotification;
use ValuePad\Letter\Support\HandlerInterface;

class CreateStaffHandler implements HandlerInterface
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
     * @param CreateStaffNotification $source
     */
    public function handle(Mailer $mailer, $source)
    {
        /**
         * @var Manager $manager
         */
        $manager = $source->getStaff()->getUser();
        $company = $source->getStaff()->getCompany();

        $mailer->queue('emails.company.create_manager', [
            'username' => $manager->getUsername(),
            'firstName' => $manager->getFirstName(),
            'password' => $source->getExtra()[CreateStaffNotification::EXTRA_PASSWORD],
            'loginUrl' => $this->config->get('app.front_end_url').'/login'
        ], function (Message $message) use ($manager, $company) {
            $message->from($company->getEmail(), $company->getName());
            $message->subject('Manager Account Created');
            $message->to($manager->getEmail(), $manager->getDisplayName());
        });
    }
}
