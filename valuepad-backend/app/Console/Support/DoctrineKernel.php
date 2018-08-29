<?php
namespace ValuePad\Console\Support;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Helper\ConfigurationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Version;
use Illuminate\Console\Application as Artisan;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 *
 *
 */
class DoctrineKernel extends ConsoleKernel
{

    protected $commands = [
        CreateSchemaCommand::class,
        UpdateCommand::class,
        DropCommand::class,
        GenerateProxiesCommand::class,
        GenerateCommand::class,
        MigrateCommand::class,
		DiffCommand::class,
        ExecuteCommand::class
    ];

    protected function getArtisan()
    {
        if ($this->artisan === null) {

            $this->artisan = new Artisan($this->app, $this->events, Version::VERSION);

            /**
             *
             * @var EntityManagerInterface $entityManager
             */
            $entityManager = $this->app->make(EntityManagerInterface::class);

            $helperSet = ConsoleRunner::createHelperSet($entityManager);

            $helperSet->set(new QuestionHelper(), 'dialog');

            $configuration = new Configuration($entityManager->getConnection());

            $migrationsConfig = $this->app->make('config')->get('doctrine.migrations', []);

            $configuration->setMigrationsDirectory($migrationsConfig['dir']);
            $configuration->setMigrationsNamespace($migrationsConfig['namespace']);
            $configuration->setMigrationsTableName($migrationsConfig['table']);

            $configuration->registerMigrationsFromDirectory($migrationsConfig['dir']);

            $helperSet->set(new ConfigurationHelper($entityManager->getConnection(), $configuration), 'configuration');

            $this->artisan->setHelperSet($helperSet);

            $this->artisan->resolveCommands($this->commands);
        }

        return $this->artisan;
    }
}
