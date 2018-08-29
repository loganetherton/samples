<?php

namespace ValuePad\Console\Project;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use ValuePad\Console\Support\Kernel as Artisan;

/**
 *
 * @author Sergei Melnikov <me@rnr.name>
 */
class ProjectTestCommand extends Command
{

    /**
     * @var array
     */
    private $warnings = [
        'Warning: Using a password on the command line interface can be insecure.'
    ];

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'project:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tests the project';

    /**
	 * @param Artisan $artisan
     */
    public function fire(Artisan $artisan)
    {
        $this->comment('Resetting the project ...');

        $artisan->call('project:reset');

        $this->info('Perfect!');

        $phpunit = getenv('PHPUNIT') ?: './vendor/bin/phpunit';

        $this->comment('Executing tests ...');
        $this->runTests($phpunit, $this->option('filter'));

        $this->info('Perfect!');
    }

    /**
     * @param string $phpunit
     * @param string|null $filter
     */
    protected function runTests($phpunit = 'phpunit', $filter = null)
    {
        $cmd = "{$phpunit} tests/Integrations/UseCasesTest.php";

        if ($filter) {
            $cmd = $cmd . " --filter='ValuePad\\\\Tests\\\\Integrations\\\\UseCasesTest::testUseCases " . "with data set \"{$filter}'";
        }

        $this->comment($cmd);

        $phpunit = new Process($cmd);
        $phpunit->setTimeout(null);
        $phpunit->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->throwError($buffer);
            } else {
                $this->output->write($buffer);
            }
        });
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'filter',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Filter which tests to run.'
            ]
        ];
    }

    /**
     * @param string $text
     */
    private function throwError($text)
    {
        $text = trim($text);

        if (! in_array($text, $this->warnings)) {
            $this->error($text);
            die();
        }

        $this->comment($text);
    }
}
