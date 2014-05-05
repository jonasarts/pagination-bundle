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

use jonasarts\Bundle\PaginationBundle\Pagination\Pagination;

class PaginationTest extends WebTestCase
{
    private $dummy;

    private function GenerateDummyData($count)
    {
        $this->dummy = array();

        for ($i = 0; $i < $count; $i++) {
            $this->dummy[] = $i;
        }
    }

    public function testPaginationConstruction()
    {
        $p = new Pagination(array(), 0);

        $this->assertTrue($p != null);
    }

    public function testPaginationRange()
    {
        $range = 10; // page navigatin window

        $this->GenerateDummyData(100);

        $p = new Pagination($this->dummy, count($this->dummy));
        
        $p->setPageRange($range);
        $p->setItemNumberPerPage(10);
        $p->setCurrentPageNumber(5); // page index value ('values' for this page are being displayed)

        $r = $p->getPaginationData();

        $this->assertTrue((max($r['pagesInRange']) - min($r['pagesInRange']) + 1) == $range);
    }

    public function testPaginationTotalCount()
    {
        $p = new Pagination(array(), 100);

        $this->assertTrue($p->getTotalCount() == 100);
    }
}