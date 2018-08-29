<?php
namespace ValuePad\Tests\Project\Core\Document\Support;

use PHPUnit_Framework_TestCase;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Support\DocumentUsageManagement;
use ReflectionClass;

class DocumentUsageManagementTest extends PHPUnit_Framework_TestCase
{
    public function testSingle()
    {
        $tracker = new DocumentUsageManagement();

        $document1 = new Document();
        $document1->setId(1);

        $document2 = new Document();
        $document1->setId(2);

        $tracker->handleSingle(null, $document2);

        $this->assertEquals(1, $this->getUsage($document2));

        $tracker->handleSingle($document2, $document1);

        $this->assertEquals(1, $this->getUsage($document1));
        $this->assertEquals(0, $this->getUsage($document2));

        $tracker->handleSingle($document1, $document1);
        $this->assertEquals(1, $this->getUsage($document1));
    }

    public function testSingleWithNewNull()
    {
        $tracker = new DocumentUsageManagement();

        $document1 = new Document();
        $document1->setId(1);

        $tracker->handleSingle(null, $document1);

        $this->assertEquals(1, $this->getUsage($document1));

        $tracker->handleSingle($document1, null);

        $this->assertEquals(0, $this->getUsage($document1));
    }

    public function testMultiple()
    {
        $tracker = new DocumentUsageManagement();

        $d1 = new Document();
        $d1->setId(1);

        $d2 = new Document();
        $d2->setId(2);

        $d3 = new Document();
        $d3->setId(3);

        $d4 = new Document();
        $d4->setId(4);

        $d5 = new Document();
        $d4->setId(5);


        $c1 = [];

        $c1[] = $d1;
        $c1[] = $d3;
        $c1[] = $d2;

        $c2 = [];
        $c2[] = $d4;
        $c2[] = $d5;
        $c2[] = $d2;

        $tracker->handleMultiple(null, $c1);
        $result = array_sum(array_map(function($d){ return $this->getUsage($d); }, $c1));
        $this->assertEquals(3, $result);

        $tracker->handleMultiple($c1, $c1);
        $result = array_sum(array_map(function($d){ return $this->getUsage($d); }, $c1));
        $this->assertEquals(3, $result);

        $tracker->handleMultiple($c1, $c2);

        $result1 = array_sum(array_map(function($d){ return $this->getUsage($d); }, $c1));
        $result2 = array_sum(array_map(function($d){ return $this->getUsage($d); }, $c2));

        $this->assertEquals(3, $result2);
        $this->assertEquals(1, $result1);

        $d6 = new Document();
        $d6->setId(6);

        $tracker->handleMultiple($c2, $d6);

        $this->assertEquals(1, $this->getUsage($d6));
    }

    public function testMultipleWithUniqueness()
    {
        $tracker = new DocumentUsageManagement();

        $d1 = new Document();
        $d1->setId(1);

        $d2 = new Document();
        $d2->setId(2);


        $c1[] = $d1;

        $c2 = [];
        $c2[] = $d2;
        $c2[] = $d2;

        $tracker->handleMultiple($c1, $c2);

        $this->assertEquals(1, $this->getUsage($d2));
    }

    public function testMultipleWithNewNull()
    {
        $tracker = new DocumentUsageManagement();

        $d1 = new Document();
        $d1->setId(1);

        $c1 = [$d1];

        $tracker->handleMultiple(null, $c1);

        $this->assertEquals(1, $this->getUsage($d1));

        $tracker->handleMultiple($c1, null);

        $this->assertEquals(0, $this->getUsage($d1));
    }

    private function getUsage(Document $document)
    {
        $reflection = new ReflectionClass($document);

        $property = $reflection->getProperty('usage');

        $property->setAccessible(true);

        return $property->getValue($document);
    }
}
