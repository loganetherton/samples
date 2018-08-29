<?php
namespace ValuePad\Debug\Controllers;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Response;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Entities\Invoice;
use ValuePad\Core\Amc\Entities\Item;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Debug\Support\BaseController;
use DateTime;

class InvoicesController extends BaseController
{
    /**
     * @param int $amcId
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function store($amcId, EntityManagerInterface $entityManager)
    {
        $invoice = new Invoice();

        $invoice->setAmount(rand(10, 100));
        $invoice->setFrom(new DateTime('-10 days'));
        $invoice->setTo(new DateTime('-2 days'));

        /**
         * @var Amc $amc
         */
        $amc = $entityManager->getReference(Amc::class, $amcId);

        $invoice->setAmc($amc);

        $builder = $entityManager->createQueryBuilder();

        $documents = $builder
            ->from(Document::class, 'd')
            ->select('d')
            ->setFirstResult(1000)
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();

        $document = array_values($documents)[rand(0, 99)];

        $invoice->setDocument($document);

        $entityManager->persist($invoice);

        $entityManager->flush();

        $builder = $entityManager->createQueryBuilder();

        /**
         * @var Order[] $orders
         */
        $orders = $builder
            ->from(Order::class, 'o')
            ->select('o')
            ->setFirstResult(700)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $items = [];

        foreach ($orders as $order){
            $item = new Item();

            $item->setAmount(rand(10, 100));
            $item->setAddress($order->getProperty()->getDisplayAddress());
            $item->setBorrowerName(object_take($order, 'borrower.displayName'));
            $item->setOrderedAt(new DateTime('-8 days'));
            $item->setCompletedAt(new DateTime('-10 months'));
            $item->setJobType($order->getJobType()->getTitle());
            $item->setFileNumber($order->getFileNumber());
            $item->setLoanNumber($order->getLoanNumber());

            $item->setOrder($order);
            $item->setInvoice($invoice);

            $entityManager->persist($item);

            $items[] = $item;
        }

        $entityManager->flush();

        $invoice->setItems($items);

        $entityManager->flush();
    }
}
