<?php
/**
 * Daemon runner class.
 */
class DaemonRunner
{
    /**
     * @var string $command Command to be executed
     */
    protected $command;

    /**
     * Constructor.
     * @param string $command Command to be executed
     */
    public function __construct($command)
    {
        $this->command = $command;
    }

    /**
     * @return string Command to be executed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Resolve a command you expect to exit with return code 0. Works like
     * @{method:resolve}, but throws if $err is nonempty. Returns only
     * $stdout and $stderr. See also @{function:execx}.
     *
     *   list($stdout, $stderr) = $future->resolvex();
     *
     * @param  float Optional timeout after which resolution will pause and
     *               execution will return to the caller.
     * @return pair  <$stdout, $stderr> pair.
     * @task resolve
     */
    public function resolvex($timeout = null) {
      list($err, $stdout, $stderr) = $this->resolve($timeout);
      if ($err) {
        $cmd = $this->command;
        throw new CommandException(
          "Command failed with error #{$err}!",
          $cmd,
          $err,
          $stdout,
          $stderr);
      }
      return array($stdout, $stderr);
    }
}