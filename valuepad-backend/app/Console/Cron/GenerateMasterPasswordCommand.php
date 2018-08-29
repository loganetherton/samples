<?php
namespace ValuePad\Console\Cron;

use ValuePad\Core\Back\Services\SettingsService;
use ValuePad\Core\User\Interfaces\PasswordEncryptorInterface;
use Maknz\Slack\Client as Slack;
use Illuminate\Config\Repository as Config;

class GenerateMasterPasswordCommand extends AbstractCommand
{
	/**
	 * @param SettingsService $settingsService
	 * @param PasswordEncryptorInterface $encryptor
	 * @param Slack $slack
	 * @param Config $config
	 */
	public function fire(
		SettingsService $settingsService,
		PasswordEncryptorInterface $encryptor,
		Slack $slack,
		Config $config
	)
	{
		$password = str_random(16);
		$settingsService->set(SettingsService::SETTING_MASTER_PASSWORD, $encryptor->encrypt($password));


		$context = $config->get('app.context');
		$link = $context === 'production' ? 'https://app.valuepad.com/login': 'https://stage.valuepad.com/login';

		if ($slack->getEndpoint()){
			$slack->createMessage()->setChannel($config->get('slack.master_password_channel'))
				->setUsername('password-bot')
				->setIcon(':valuepad:')
				->send(sprintf(
					'The new master password for ValuePad on %s is `%s`.'."\n".'Go forth and <%s|login>.',
					$context, $password, $link));
		}
	}
}
