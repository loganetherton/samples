<?php
namespace ValuePad\DAL\Support;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;

class ConnectionFactory
{

    /**
     * @param array $dbConfig
     * @param Configuration $configuration
     * @return Connection
     */
    public function __invoke(array $dbConfig, Configuration $configuration)
    {
        return DriverManager::getConnection($dbConfig, $configuration, new EventManager());
    }
}
