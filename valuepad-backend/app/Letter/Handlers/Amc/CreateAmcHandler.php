<?php
namespace ValuePad\Letter\Handlers\Amc;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use ValuePad\Core\Amc\Notifications\CreateAmcNotification;
use ValuePad\Letter\Support\HandlerInterface;

class CreateAmcHandler implements HandlerInterface
{
    /**
     * @param Mailer $mailer
     * @param CreateAmcNotification $source
     */
    public function handle(Mailer $mailer, $source)
    {
        $amc = $source->getAmc();

        $mailer->queue('emails.amc.create_amc', [
            'username' => $amc->getUsername(),
            'companyName' => $amc->getCompanyName(),
            'email' => $amc->getEmail(),
            'phone' => $amc->getPhone(),
            'fax' => $amc->getFax() ?? '',
            'address' => $amc->getAddress1().', '.$amc->getCity().', '.$amc->getState()->getCode().' '.$amc->getZip(),
            'lenders' => $amc->getLenders()
        ], function(Message $message) use ($amc){
            $message->from($amc->getEmail(), $amc->getCompanyName());
            $message->subject('New AMC Sign Up on ValuePad');
            $message->to('approvals@appraisalscope.com');
        });
    }
}
