<?php
namespace ValuePad\Console\Support;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSchemaCommand extends CreateCommand
{
    protected function executeSchemaCommand(InputInterface $input, OutputInterface $output, SchemaTool $schemaTool, array $metadatas)
    {
        $emHelper = $this->getHelper('em');

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $emHelper->getEntityManager();

        $em->getConnection()->query('SET FOREIGN_KEY_CHECKS=0');
        $result = parent::executeSchemaCommand($input, $output, $schemaTool, $metadatas);
        $em->getConnection()->query('SET FOREIGN_KEY_CHECKS=1');

        return $result;
    }
}
