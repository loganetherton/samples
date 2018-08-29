<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Services\BranchService;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161109175430 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $branchService = app(BranchService::class);
        $em = app(EntityManagerInterface::class);
        $companies = $em->createQueryBuilder()
            ->select('c')
            ->from(Company::class, 'c')
            ->orderBy('c.id')
            ->getQuery()
            ->iterate();

        foreach ($companies as $company) {
            $branchService->createDefault($company[0]);
        }

        $companyConstraint = $this->connection->fetchColumn('
            SELECT `CONSTRAINT_NAME`
            FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
            WHERE `TABLE_NAME`=? AND `COLUMN_NAME`=?
        ', ['companies_staff', 'company_id']);

        $this->addSql('ALTER TABLE `companies_staff` DROP FOREIGN KEY '.$companyConstraint);

        $staffConstraint = $this->connection->fetchColumn('
            SELECT `CONSTRAINT_NAME`
            FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
            WHERE `TABLE_NAME`=? AND `COLUMN_NAME`=? AND `REFERENCED_TABLE_NAME` IS NOT NULL
        ', ['companies_staff_privileges', 'staff_id']);

        $this->addSql('ALTER TABLE `companies_staff_privileges` DROP FOREIGN KEY '.$staffConstraint);

        $this->addSql('RENAME TABLE `companies_staff` TO `branches_staff`');
        $this->addSql('RENAME TABLE `companies_staff_privileges` TO `branches_staff_privileges`');
        $this->addSql('ALTER TABLE `branches_staff` CHANGE `company_id` `branch_id` int(11) DEFAULT NULL');

        $this->addSql('ALTER TABLE `branches_staff` ADD CONSTRAINT `'.$companyConstraint.'` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)');
        $this->addSql('ALTER TABLE `branches_staff_privileges` ADD CONSTRAINT `'.$staffConstraint.'` FOREIGN KEY (`staff_id`) REFERENCES `branches_staff` (`id`)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
