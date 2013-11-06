<?php

/*
 * This file is part of the Pagination bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\PaginationManager\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PaginationManagerTest extends WebTestCase
{
    /*
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    private $pm;

    private $dummy;

    private function GenerateDummyData($count)
    {
        $this->dummy = array();

        for ($i = 0; $i < $count; $i++) {
            $this->dummy[] = $i;
        }
    }

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
        $this->pm = static::$kernel->getContainer()
            ->get('pagination_manager');
    }

    // pagionation tests

    public function testPaginationData()
    {
        $this->GenerateDummyData(100);

        $p = $this->pm->getPagination($this->dummy, count($this->dummy), 1, 10, 10);

        //var_dump($p->getPaginationData());
        
        $r = $p->getPaginationData();

        $this->assertEquals($r['numItemsPerPage'], 10);
        $this->assertEquals($r['pageCount'], 10);
        $this->assertEquals($r['totalCount'], 100);
        $this->assertEquals($r['first'], 1);
        $this->assertEquals($r['current'], 1);
        $this->assertEquals($r['last'], 10);
    }

    public function testPaginationDataPrevious()
    {
        // previous must exist, so use a offset > 1
        $this->GenerateDummyData(30);

        $p = $this->pm->getPagination($this->dummy, count($this->dummy), 2, 10, 10);
        
        $r = $p->getPaginationData();

        $this->assertEquals($r['previous'], 1);
    }

    public function testPaginationDataNext()
    {
        // next must exist, so use a offset < max page
        $this->GenerateDummyData(30);

        $p = $this->pm->getPagination($this->dummy, count($this->dummy), 2, 10, 10);
        
        $r = $p->getPaginationData();

        $this->assertEquals($r['next'], 3);
    }

    public function testPaginationDataRage()
    {
        $this->GenerateDummyData(100);

        $p = $this->pm->getPagination($this->dummy, count($this->dummy), 10, 10, 10);
        
        $r = $p->getPaginationData();

        $this->assertEquals($r['firstPageInRange'], 1);
        $this->assertEquals($r['lastPageInRange'], 10);
    }

    public function testPaginationDataItem()
    {
        $this->GenerateDummyData(100);

        $p = $this->pm->getPagination($this->dummy, count($this->dummy), 5, 10, 10);
        
        $r = $p->getPaginationData();

        $this->assertEquals($r['firstItemNumber'], 41);
        $this->assertEquals($r['lastItemNumber'], 50);
    }

    public function testPaginationDataItemBorder()
    {
        $this->GenerateDummyData(103);

        $p = $this->pm->getPagination($this->dummy, count($this->dummy), 11, 10, 10);
        
        $r = $p->getPaginationData();

        $this->assertEquals($r['firstItemNumber'], 101);
        $this->assertEquals($r['lastItemNumber'], 103);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->em->close();
    }
}