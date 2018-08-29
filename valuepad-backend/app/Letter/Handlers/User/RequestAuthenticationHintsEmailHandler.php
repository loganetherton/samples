<?php
namespace ValuePad\Letter\Handlers\User;
use Illuminate\Mail\Mailer;
use ValuePad\Core\User\Emails\RequestAuthenticationHintsEmail;
use ValuePad\Core\User\Interfaces\IndividualInterface;
use ValuePad\Letter\Support\HandlerInterface;
use Illuminate\Config\Repository as Config;
use Illuminate\Mail\Message;

class RequestAuthenticationHintsEmailHandler implements HandlerInterface
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
     * @param RequestAuthenticationHintsEmail $source
     */
    public function handle(Mailer $mailer, $source)
    {
        $data = [];

        foreach ($source->getTokens() as $token){

            $user = $token->getUser();

            $row = [
                'name' => $user->getDisplayName(),
                'username' => $user->getUsername(),
                'actionUrl' => $this->config->get('app.front_end_url')
                    .'/reset-password?token='.$token->getValue(),
            ];

            if ($user instanceof IndividualInterface){
                $row['firstName'] = $user->getFirstName();
                $row['lastName'] = $user->getLastName();
            }

            $data[] = $row;
        }

        $mailer->queue('emails.users.request_authentication_hints', ['data' => $data], function(Message $message) use ($source) {
            $message->from($source->getSender()->getEmail(), $source->getSender()->getName());

            foreach ($source->getRecipients() as $recipient){
                $message->to($recipient->getEmail(), $recipient->getName());
            }

            $message->subject('Trouble Sign-In');
        });
    }
}
