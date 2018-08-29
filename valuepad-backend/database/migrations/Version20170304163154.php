<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Support\Tracker;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Company\Entities\Company;


/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170304163154 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        
        /*
                We ended up running this manually:
                UPDATE
                    orders o
                LEFT JOIN
                    users u ON u.id = o.assignee_id
                SET
                    o.tinAtCompletion = u.tin
                WHERE
                    INSTR(o.workflow, '"completed"') > 0
                    AND o.tinAtCompletion IS NULL
                    AND u.tin IS NOT NULL
                */
                // // Update all completed orders
                // $em = app(EntityManagerInterface::class);

                // $orders = $em->createQueryBuilder()
                //     ->select('o')
                //     ->from(Order::class, 'o')
                //     ->getQuery()
                //     ->iterate();

                // $tracker = new Tracker($orders, 10);

                // foreach ($tracker as $order){
                //     /**
                //      * @var Order $order
                //      */
                //     $order = $order[0];
                //     $assignee = $order->getAssignee();
                //     $tin = '';
                //     $processStatuses = $order->getWorkflow()->toArray();
                //     // Only run for completed or reviewed
                //     if (in_array('completed', $processStatuses)) {
                //         $staff = $order->getStaff();
                //         // Use TIN of appraiser
                //         if ($staff) {
                //             $tin = $staff->getCompany()->getTaxId();
                //         } else if ($assignee instanceof Appraiser) {
                //             $tin = $assignee->getTaxIdentificationNumber();
                //         } else {
                //             $tin = null;
                //         }

                //         if ($tin) {
                //             $order->setTinAtCompletion($tin);
                //         }
                //     }
                //     if ($tracker->isTime()) {
                //         $em->flush();
                //         $em->clear();
                //     }
                // }

                // $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // Roll back one more time to drop the column entirely
    }
}
