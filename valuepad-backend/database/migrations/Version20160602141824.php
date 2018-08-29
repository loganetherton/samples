<?php
namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Log\Entities\Log;
use ValuePad\Core\Log\Extras\Extra;
use ValuePad\Support\Tracker;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160602141824 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
		/**
		 * @var EntityManagerInterface $em
		 */
        $em = app(EntityManagerInterface::class);

		$logs = $em->createQueryBuilder()
			->select('l')
			->from(Log::class, 'l')
			->getQuery()
			->iterate();


		$tracker = new Tracker($logs, 100);

		foreach ($tracker as $log){

			/**
			 * @var Log $log
			 */
			$log = $log[0];

			$data = $log->getExtra()->getData();
			unset($data['client']);

			$order = $log->getOrder();

			if ($order){
				$customer = $order->getCustomer()->getName();
			} else {
				$customer = $log->getUser()->getName();
			}

			$data[Extra::CUSTOMER] = $customer;

			$extra = new Extra();
			$extra->setData($data);

			$log->setExtra($extra);

			if ($tracker->isTime()){
				$em->flush();
				$em->clear();
			}
		}

		$em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
