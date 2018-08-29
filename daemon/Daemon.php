<?php
/**
 * Abstract daemon class.
 * This class should be a base for all daemons.
 */
Yii::import('application.components.worker.*');
Yii::import('application.workers.*');

abstract class Daemon
{
    protected $argv;
    protected $traceMode;
    protected $traceMemory;
    protected $verbose = true;

    /**
     * @var boolean $sighandlerInstalled Whether signal handler is installed.
     */
    protected $sighandlerInstalled;

    /**
     * Constructor.
     */
    public function __construct()
    {
        declare(ticks = 1);

        if(!$this->sighandlerInstalled) {
            $this->sighandlerInstalled = true;

            pcntl_signal(SIGINT,  array(__CLASS__, 'exitOnSignal'));
            pcntl_signal(SIGTERM, array(__CLASS__, 'exitOnSignal'));
        }
    }

    /**
     * Handle exit signals.
     * @param integer $signo Sent signal
     */
    public static function exitOnSignal($signo)
    {
        // Normally, PHP doesn't invoke destructors when existing in response to
        // a signal. This forces it to do so, so we have a fighting chance of
        // releasing any locks, leases or resources on our way out.
        exit(128 + $signo);
    }

    /**
     * Put a daemon to sleep.
     * @param integer $duration Sleep duration
     */
    final protected function sleep($duration)
    {
        $this->stillWorking();

        while($duration > 0) {
            sleep(min($duration, 60));
            $duration -= 60;
            $this->stillWorking();
        }
    }

    /**
     * Send Keep-Alive signal to daemon overseer.
     */
    final public function stillWorking()
    {
        if(!posix_isatty(STDOUT)) {
            posix_kill(posix_getppid(), SIGUSR1);
        }
    }

    /**
     * Log message.
     * @param string $message Log message
     */
    protected function log($message)
    {
        if($this->verbose) {
            $daemon = get_class($this);
            fprintf(STDERR, '%s', "<VERB> {$daemon} {$message}\n");
        }
    }

    /**
     * Execute daemon.
     */
    final public function execute()
    {
        $this->run();
    }

    /**
     * Run daemon implementation.
     */
    abstract public function run();
}