<?php
namespace ValuePad\Console\Support;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use ValuePad\Console\Cron\CleanEmailFrequenciesCommand;
use ValuePad\Console\Cron\DeleteOldTransactionsCommand;
use ValuePad\Console\Cron\DestroyExpiredSessionsCommand;
use ValuePad\Console\Cron\DestroyUnusedDocumentsCommand;
use ValuePad\Console\Cron\FixPropertiesCoordinatesCommand;
use ValuePad\Console\Cron\GenerateAmcMonthlyInvoicesCommand;
use ValuePad\Console\Cron\GenerateMasterPasswordCommand;
use ValuePad\Console\Cron\ImportAscDatabaseCommand;
use ValuePad\Console\Cron\MarkReturnedAppraisersAsAvailableCommand;
use ValuePad\Console\Cron\MoveOrdersPassedDueToLateQueueCommand;
use ValuePad\Console\Cron\TryAllAttemptsCommand;
use ValuePad\Console\Cron\UnacceptedReminderCommand;
use ValuePad\Console\Project\ProjectGeneratePastAmcInvoices;
use ValuePad\Console\Project\ProjectOneTimeUpdateCommand;
use ValuePad\Console\Project\ProjectResetCommand;
use ValuePad\Console\Project\ProjectTestCommand;
use ValuePad\Support\DefaultEnvironmentDetectorReplacerTrait;
use ValuePad\Support\RegisterLogglyViaBootstrapperTrait;

/**
 * @author Sergei Melnikov <me@rnr.name>
 */
class Kernel extends ConsoleKernel
{
    use DefaultEnvironmentDetectorReplacerTrait;
	use RegisterLogglyViaBootstrapperTrait;

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ProjectTestCommand::class,
        ProjectResetCommand::class,
		ProjectOneTimeUpdateCommand::class,
		ImportAscDatabaseCommand::class,
		DestroyUnusedDocumentsCommand::class,
		DestroyExpiredSessionsCommand::class,
		GenerateMasterPasswordCommand::class,
		MarkReturnedAppraisersAsAvailableCommand::class,
		MoveOrdersPassedDueToLateQueueCommand::class,
		FixPropertiesCoordinatesCommand::class,
		CleanEmailFrequenciesCommand::class,
		GenerateAmcMonthlyInvoicesCommand::class,
        TryAllAttemptsCommand::class,
        DeleteOldTransactionsCommand::class,
        UnacceptedReminderCommand::class,
        ProjectGeneratePastAmcInvoices::class
    ];

    /**
     *
     * @param Application $app
     * @param Dispatcher $events
     */
    public function __construct(Application $app, Dispatcher $events)
    {
		$this->bootstrappers = $this->registerLogglyViaBootstrapper($this->bootstrappers);
        $this->bootstrappers = $this->replaceDefaultDetectEnvironmentBootstrapper($this->bootstrappers);
        parent::__construct($app, $events);
    }

	/**
	 * @param Schedule $schedule
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('cron:import-asc-database')->dailyAt('00:05:00');
		$schedule->command('cron:destroy-unused-documents')->dailyAt('01:00:00');
		$schedule->command('cron:destroy-expired-sessions')->dailyAt('02:00:00');
		$schedule->command('cron:generate-master-password')->twiceDaily();
		$schedule->command('cron:mark-returned-appraisers-as-available')->hourly();
		$schedule->command('cron:fix-properties-coordinates')->twiceDaily();
		$schedule->command('cron:clean-email-frequencies')->daily();
		$schedule->command('cron:generate-amc-monthly-invoices')->monthly();
        $schedule->command('cron:try-all-attempts')->everyMinute();
        $schedule->command('cron:delete-old-transactions')->daily();
        $schedule->command('cron:unaccepted-reminder')->everyTenMinutes();
	}
}
