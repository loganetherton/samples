<?php
namespace ValuePad\DAL\Support;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use ValuePad\DAL\Support\Metadata\CompositeDriver;
use ValuePad\DAL\Support\Metadata\PackageDriver;
use DoctrineExtensions\Query\Mysql\Year as MysqlYear;
use DoctrineExtensions\Query\Sqlite\Year as SqliteYear;
use DoctrineExtensions\Query\Mysql\Month as MysqlMonth;
use DoctrineExtensions\Query\Sqlite\Month as SqliteMonth;
use RuntimeException;
use ValuePad\DAL\Support\Metadata\SimpleDriver;

/**
 *
 *
 */
class DoctrineServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('doctrine:configuration', function (ContainerInterface $container) {
            return $this->createConfiguration($this->getSettings($container), $container);
        });

        $this->app->singleton('doctrine:connection', function (ContainerInterface $container) {

            /**
             *
             * @var Configuration $configuration
             */
            $configuration = $container->make('doctrine:configuration');

            $factory = new ConnectionFactory();

            $settings = $this->getSettings($container);
            $dbConfig = $settings['connections'][$settings['db']];

            return $factory($dbConfig, $configuration);
        });

        $this->app->singleton(EntityManagerInterface::class, function (ContainerInterface $container) {

            /**
             * @var Connection $connection
             */
            $connection = $container->make('doctrine:connection');

            /**
             * @var Configuration $configuration
             */
            $configuration = $container->make('doctrine:configuration');

            return $this->createEntityManager($connection, $configuration, $container);
        });
    }

    /**
     *
     * @param ContainerInterface $container
     * @return array
     */
    private function getSettings(ContainerInterface $container)
    {
        return $container->make('config')->get('doctrine', []);
    }

    /**
     *
     * @param array $config
     * @param ContainerInterface $container
     * @return Configuration
     */
    private function createConfiguration(array $config, ContainerInterface $container)
    {
        $setup = Setup::createConfiguration();

        $cache = new $config['cache']();

        $setup->setMetadataCacheImpl($cache);
        $setup->setQueryCacheImpl($cache);

        $setup->setProxyDir($config['proxy']['dir']);
        $setup->setProxyNamespace($config['proxy']['namespace']);
        $setup->setAutoGenerateProxyClasses(array_get($config, 'proxy.auto', false));

        $packages = $container->make('config')->get('app.packages');

        $setup->setMetadataDriverImpl(new CompositeDriver([
			new PackageDriver($packages),
			new SimpleDriver(array_take($config, 'entities', []))
		]));

        $setup->setNamingStrategy(new UnderscoreNamingStrategy());
		$setup->setDefaultRepositoryClassName(DefaultRepository::class);


		$driver = $config['connections'][$config['db']]['driver'];

		if ($driver == 'pdo_sqlite'){
			$setup->addCustomDatetimeFunction('YEAR', SqliteYear::class);
			$setup->addCustomDatetimeFunction('MONTH', SqliteMonth::class);
		} else if ($driver == 'pdo_mysql'){
			$setup->addCustomDatetimeFunction('YEAR', MysqlYear::class);
			$setup->addCustomDatetimeFunction('MONTH', MysqlMonth::class);
		} else {
			throw new RuntimeException('Unable to add functions under unknown driver "'.$driver.'".');
		}


		return $setup;
    }

    /**
     *
     * @param Connection $connection
     * @param Configuration $configuration
     * @param ContainerInterface $container
     * @return EntityManager
     */
    private function createEntityManager(Connection $connection, Configuration $configuration, ContainerInterface $container)
    {
        $packages = $container->make('config')->get('app.packages');

        $em = EntityManager::create($connection, $configuration);

        $this->registerTypes($em->getConnection(), $packages, $container->make('config')->get('doctrine.types', []));

        return new EntityManagerDecorator($em);
    }

    /**
     *
     * @param Connection $connection
     * @param array $packages
     * @param array $extra
     */
    private function registerTypes(Connection $connection, array $packages, array $extra = [])
    {
        foreach ($packages as $package) {
            $path = app_path('DAL/' . str_replace('\\', '/', $package) . '/Types');
            $typeNamespace = 'ValuePad\DAL\\' . $package . '\Types';

            if (! file_exists($path)) {
                continue;
            }

            $finder = new Finder();

            /**
             *
             * @var SplFileInfo[] $files
             */
            $files = $finder->in($path)
                ->files()
                ->name('*.php');

            foreach ($files as $file) {
                $name = cut_string_right($file->getFilename(), '.php');

                $typeClass = $typeNamespace . '\\' . $name;

                if (! class_exists($typeClass)) {
                    continue;
                }

                if (Type::hasType($typeClass)) {
                    Type::overrideType($typeClass, $typeClass);
                } else {
                    Type::addType($typeClass, $typeClass);
                }

                $connection->getDatabasePlatform()->registerDoctrineTypeMapping($typeClass, $typeClass);
            }
        }

        foreach ($extra as $type){
            if (Type::hasType($type)) {
                Type::overrideType($type, $type);
            } else {
                Type::addType($type, $type);
            }
        }
    }
}
