<?php
/**
 * Daemon reference.
 */
class DaemonReference
{
    /**
     * @var string $name Daemon name
     */
    protected $name;

    /**
     * @var integer $pid Daemon PID
     */
    protected $pid;

    /**
     * @var integer $start Daemon start timestamp
     */
    protected $start;

    /**
     * @var string $pidFile PID file path
     */
    protected $pidFile;

    /**
     * Create new daemon reference from dictionary.
     * @param array $dictionary Daemon dictionary
     * @return object DaemonReference
     */
    public static function newFromDictionary(array $dict)
    {
        $ref = new DaemonReference;

        $ref->name  = Arr::get($dict, 'name', 'Unknown');
        $ref->pid   = Arr::get($dict, 'pid');
        $ref->start = Arr::get($dict, 'start');

        return $ref;
    }

    /**
     * @return string Daemon name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return integer Daemon PID
     */
    public function getPID()
    {
        return $this->pid;
    }

    /**
     * @return integer Daemon start timestamp
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set PID file path.
     * @param string $pidFile PID file path
     * @return object DaemonReference
     */
    public function setPIDFile($pidFile)
    {
        $this->pidFile = $pidFile;

        return $this;
    }

    /**
     * @return string PID file path
     */
    public function getPIDFile()
    {
        return $this->pidFile;
    }

    /**
     * @return boolean Whether daemon is running
     */
    public function isRunning()
    {
        return $this->isProcessRunning($this->getPID());
    }

    /**
     * Whether daemon is running.
     * @param integer $pid Daemon PID
     * @return boolean Whether deamon is running
     */
    public function isProcessRunning($pid)
    {
        if(!$pid) {
            return false;
        }

        if(function_exists('posix_kill')) {
            // This may fail if we can't signal the process because we are running as
            // a different user (for example, we are 'apache' and the process is some
            // other user's, or we are a normal user and the process is root's), but
            // we can check the error code to figure out if the process exists.
            $isRunning = posix_kill($pid, 0);

            if(posix_get_last_error() == 1) {
                // "Operation Not Permitted", indicates that the PID exists. If it
                // doesn't, we'll get an error 3 ("No such process") instead.
                $isRunning = true;
            }
        }
        else {
            // If we don't have the posix extension, just exec.
            list($err) = exec_manual('ps %s', $pid);
            $isRunning = ($err == 0);
        }

        return $isRunning;
    }
}