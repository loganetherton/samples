<?php
/**
 * Oversees a daemon and restarts it if it fails.
 */
Yii::import('application.daemons.*');
Yii::import('application.futures.*');
Yii::import('application.helpers.*');

class DaemonOverseer extends CComponent
{
    const EVENT_DID_LAUNCH    = 'onDaemonLaunch';
    const EVENT_DID_LOG       = 'onDaemonLogMessage';
    const EVENT_DID_HEARTBEAT = 'onDaemonHeartbeat';
    const EVENT_WILL_EXIT     = 'onDaemonExit';

    const HEARTBEAT_WAIT      = 120;
    const RESTART_WAIT        = 5;

    /**
     * @var boolean $traceMode Trace mode.
     */
    protected $traceMode = false;

    /**
     * @var boolean $traceMemory Trace memory.
     */
    protected $traceMemory = false;

    /**
     * @var boolean $verbose Verbose mode.
     */
    protected $verbose = false;

    /**
     * @var boolean $daemonize Run as a daemon.
     */
    protected $daemonize = false;

    /**
     * @var string $pd PID directory.
     */
    protected $pd;

    /**
     * @var array $args Command line parameters for launch command.
     */
    protected $args;

    /**
     * @var array $originalArgs Command line parameters copy.
     */
    protected $originalArgs;

    /**
     * @var string $daemon Daemon name
     */
    protected $daemon;

    /**
     * @var string $daemonId Daemon id.
     */
    protected $daemonId;

    /**
     * @var integer $childPID Child process PID.
     */
    protected $childPID;

    /**
     * @var boolean $signaled
     */
    protected $signaled;

    /**
     * @var integer $deadline Deadline timestamp.
     */
    protected $deadline;

    /**
     * @var integer $deadlineTimeout Deadline timeout.
     */
    protected $deadlineTimeout = 86400;     // 24 hours

    /**
     * @var integer $killDelay
     */
    protected $killDelay = 3;

    /**
     * @var integer $heartbeat Next heartbeat timestamp.
     */
    protected $heartbeat;

    /**
     * @var integer $captureBufferSize Capture buffer size.
     */
    protected $captureBufferSize = 65536;

    /**
     * @var object $instance DaemonOverseer instance.
     */
    protected static $instance;

    /**
     * Class constructor.
     * @param object $daemonLauncher Daemon launch command
     * @param array $args Command line parameters for launch command.
     */
    public function __construct($daemonLauncher, $args)
    {
        // Store original arguments (without daemon name) in order to pass them to an event
        $this->originalArgs = array_slice($args, 1);

        // Get daemon name (first argument)
        $this->daemon = array_shift($args);

        if(!$this->daemon) {
            throw new CException('Missing daemon name');
        }

        // Set flags
        $this->traceMode    = $daemonLauncher->trace;
        $this->traceMemory  = $daemonLauncher->traceMemory;
        $this->verbose      = $daemonLauncher->verbose;
        $this->daemonize    = $daemonLauncher->daemonize;
        $this->pd           = $daemonLauncher->pd;
        $this->args         = $args;

        if(self::$instance) {
            throw new CException('You may not instantiate more than one Overseer per process.');
        }

        self::$instance = $this;
    }

    /**
     * Initialize the daemon overseer.
     */
    protected function init()
    {
        if($this->daemonize) {
            // We need to get rid of these or the daemon will hang when we TERM it
            // waiting for something to read the buffers.
            fclose(STDOUT);
            fclose(STDERR);
            ob_start();

            // Fork the process
            $pid = pcntl_fork();

            if($pid === -1) {
                // Error - unable to fork
                throw new CException('Unable to fork!');
            }
            elseif($pid) {
                // Parent process
                exit(0);
            }
        }

        if($this->pd) {
            $desc = array(
                'name'  => $this->daemon,
                'pid'   => getmypid(),
                'start' => time(),
            );

            // Save process details
            file_put_contents($this->pd.'/daemon.'.getmypid(), CJSON::encode($desc));
        }

        $this->daemonId = $this->generatedaemonId();

        // Dispatch events
        $this->dispatchEvent(self::EVENT_DID_LAUNCH, array('argv'=>CJSON::encode($this->originalArgs)));

        declare(ticks = 1);

        pcntl_signal(SIGUSR1, array($this, 'didReceiveKeepAliveSignal'));
        pcntl_signal(SIGINT,  array($this, 'didReceiveTerminalSignal'));
        pcntl_signal(SIGTERM, array($this, 'didReceiveTerminalSignal'));
    }

    /**
     * Run the daemon overseer.
     */
    public function run()
    {
        $this->init();

        if($this->shouldRunSilently()) {
            echo "Running daemon '{$this->daemon}' silently. Use '--trace' or '--verbose' to produce debugging output.\n";
        }

        $argv = array();
        $argv[] = sprintf('exec ./yiic execDaemon index %s', $this->daemon);

        foreach($this->args as $k => $arg) {
            $argv[] = sprintf('%s', $arg);
        }

        $command = implode(' ', $argv);

        while(true) {
            $this->logMessage('INIT', 'Starting process');

            $future = new ExecFuture('%s', $command);
            $future->setCWD('.');
            $future->setStdoutSizeLimit($this->captureBufferSize);
            $future->setStderrSizeLimit($this->captureBufferSize);

            $this->deadline  = time() + $this->deadlineTimeout;
            $this->heartbeat = time() + self::HEARTBEAT_WAIT;

            $future->isReady();
            $this->childPID = $future->getPID();

            do {
                do {
                    if($this->traceMemory) {
                        $memoryUsage = number_format(Yii::getLogger()->getMemoryUsage() / 1024, 1, '.', ' ');
                        $this->logMessage('RAMS', "Overseer Memory Usage: {$memoryUsage} KB");
                    }

                    // We need a shortish timeout here so we can run the tick handler frequently in order to process signals.
                    $result = $future->resolve(1);

                    list($stdout, $stderr) = $future->read();
                    $stdout = trim($stdout);
                    $stderr = trim($stderr);

                    if(strlen($stdout)) {
                        $this->logMessage('STDO', $stdout);
                    }

                    if(strlen($stderr)) {
                        $this->logMessage('STDE', $stderr);
                    }

                    $future->discardBuffers();

                    if($result !== null) {
                        list($err) = $result;

                        if($err) {
                            $this->logMessage('FAIL', "Process exited with error {$err}");
                        }
                        else {
                            $this->logMessage('DONE', 'Process exited successfully');
                        }

                        break 2;
                    }

                    if($this->heartbeat < time()) {
                        $this->heartbeat = time() + self::HEARTBEAT_WAIT;
                        $this->dispatchEvent(self::EVENT_DID_HEARTBEAT);
                    }
                } while(time() < $this->deadline);

                $this->logMessage('HANG', 'Hang detected. Restarting process');
                $this->annihilateProcessGroup();
            } while(false);

            $this->logMessage('WAIT', 'Waiting to restart process');

            sleep(self::RESTART_WAIT);
        }
    }

    /**
     * Handle keep-alive signal.
     * Keep-Alive signal is sent by daemon worker.
     * @param integer $signo Sent signal
     */
    public function didReceiveKeepAliveSignal($signo)
    {
        $this->deadline = time() + $this->deadlineTimeout;
    }

    /**
     * Handle terminal signal.
     * @param integer $signal Sent signal
     */
    public function didReceiveTerminalSignal($signo)
    {
        if($this->signaled) {
            exit(128 + $signo);
        }

        $this->signaled = true;

        $signame = Signal::getSignalName($signo);

        if($signame) {
            $sigmsg = "Shutting down in response to signal {$signo} ({$signame})";
        }
        else {
            $sigmsg = "Shutting down in response to signal {$signo}";
        }

        $this->logMessage('EXIT', $sigmsg, $signo);

        if(is_resource(STDOUT)) {
            fflush(STDOUT);
            fclose(STDOUT);
        }

        if(is_resource(STDERR)) {
            fflush(STDERR);
            fclose(STDERR);
        }

        $this->annihilateProcessGroup();

        $this->dispatchEvent(self::EVENT_WILL_EXIT);

        exit(128 + $signo);
    }

    /**
     * @return boolean Whether daemon should be ran silently
     */
    protected function shouldRunSilently()
    {
        if($this->traceMode || $this->verbose) {
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * @return string A unique daemon Id
     */
    protected function generatedaemonId()
    {
        return substr(getmypid().':'.Text::random('alnum', 12), 0, 12);
    }

    /**
     * Kill the process.
     */
    protected function annihilateProcessGroup()
    {
        $pid = $this->childPID;
        $pgid = posix_getpgid($pid);

        $cpid = getmypid();
        $cpgid = posix_getpgid($cpid);

        if($pid && $pgid) {
            // NOTE: On Ubuntu, 'kill' does not recognize the use of "--" to
            // explicitly delineate PID/PGIDs from signals. We don't actually need it,
            // so use the implicit "kill -TERM -pgid" form instead of the explicit
            // "kill -TERM -- -pgid" form.
            exec("kill -TERM -{$pgid}");
            sleep($this->killDelay);

            // On OSX, we'll get a permission error on stderr if the SIGTERM was
            // successful in ending the life of the process group, presumably because
            // all that's left is the daemon itself as a zombie waiting for us to
            // reap it. However, we still need to issue this command for process
            // groups that resist SIGTERM. Rather than trying to figure out if the
            // process group is still around or not, just SIGKILL unconditionally and
            // ignore any error which may be raised.
            exec("kill -KILL -{$pgid} 2>/dev/null");
            $this->childPID = null;
        }
    }

    /**
     * Log message.
     * @param string $type Message type
     * @param string $message Message
     * @param string $context Message context
     */
    protected function logMessage($type, $message, $context = null)
    {
        if(!$this->shouldRunSilently()) {
            printf("%s [%s] %s\n", date('Y-m-d H:i:s'), $type, $message);
        }

        $this->dispatchEvent(self::EVENT_DID_LOG, array(
            'type'    => $type,
            'message' => $message,
            'context' => $context,
        ));
    }

    /**
     * Dispatch an event to event listeners.
     * @param string $type Event type
     * @param array $params Event parameters
     */
    protected function dispatchEvent($type, array $params = array())
    {
        $data = array(
            'id'            => $this->daemonId,
            'daemonClass'   => $this->daemon,
            'childPID'      => $this->childPID,
        ) + $params;

        $event = new CEvent($this, $data);

        if($this->hasEventHandler($type)) {
            $this->$type($event);
        }
    }

    /**
     * Raise an event on daemon launch.
     * @param object $event Event object
     */
    public function onDaemonLaunch($event)
    {
        $this->raiseEvent(self::EVENT_DID_LAUNCH, $event);
    }

    /**
     * Raise an event on log message.
     * @param object $event Event object
     */
    public function onDaemonLogMessage($event)
    {
        $this->raiseEvent(self::EVENT_DID_LOG, $event);
    }

    /**
    * Raise an event on heartbeat.
    * @param object $event Event object
     */
    public function onDaemonHeartbeat($event)
    {
        $this->raiseEvent(self::EVENT_DID_HEARTBEAT, $event);
    }

    /**
    * Raise an event on daemon exit.
    * @param object $event Event object
     */
    public function onDaemonExit($event)
    {
        $this->raiseEvent(self::EVENT_WILL_EXIT, $event);
    }
}