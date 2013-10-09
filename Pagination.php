<?php

/*
 * This file is part of the Pagination bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\PaginationBundle;

use Closure;

/**
 * Pagination class
 */
class Pagination extends AbstractPagination
{
    /**
     * Pagination page range
     *
     * @var integer
     */
    private $range = 5;

    /**
     * Closure which is executed to render pagination
     *
     * @var Closure
     */
    public $renderer;

    public function setPageRange($range)
    {
        $this->range = intval(abs($range));
    }

    public function getPaginationData()
    {
        //$pageCount = intval(ceil($this->totalCount / $this->numItemsPerPage));
        if ($this->getItemNumberPerPage() > 0) {
            $pageCount = intval(ceil($this->totalCount / $this->getItemNumberPerPage()));
        } else {
            $pageCount = 1;
        }
        //$current = $this->currentPageNumber;
        $current = $this->getCurrentPageNumber();

        if ($this->range > $pageCount) {
            $this->range = $pageCount;
        }

        $delta = ceil($this->range / 2);

        if ($current - $delta > $pageCount - $this->range) {
            $pages = range($pageCount - $this->range + 1, $pageCount);
        } else {
            if ($current - $delta < 0) {
                $delta = $current;
            }

            $offset = $current - $delta;
            $pages = range($offset + 1, $offset + $this->range);
        }

        $viewData = array(
            'last' => $pageCount,
            'current' => $current,
            //'numItemsPerPage' => $this->numItemsPerPage,
            'numItemsPerPage' => $this->getItemNumberPerPage(),
            'first' => 1,
            'pageCount' => $pageCount,
            'totalCount' => $this->totalCount,
        );
        //$viewData = array_merge($viewData, $this->paginatorOptions, $this->customParameters);

        if ($current - 1 > 0) {
            $viewData['previous'] = $current - 1;
        }

        if ($current + 1 <= $pageCount) {
            $viewData['next'] = $current + 1;
        }
        $viewData['pagesInRange'] = $pages;
        $viewData['firstPageInRange'] = min($pages);
        $viewData['lastPageInRange']  = max($pages);

        if ($this->getItems() !== null) {
            $viewData['currentItemCount'] = $this->count();
            //$viewData['firstItemNumber'] = (($current - 1) * $this->numItemsPerPage) + 1;
            $viewData['firstItemNumber'] = (($current - 1) * $this->getItemNumberPerPage()) + 1;
            $viewData['lastItemNumber'] = $viewData['firstItemNumber'] + $viewData['currentItemCount'] - 1;
        }

        return $viewData;
    }

    /**
     * Constructor
     */
    public function __construct(array $items, $totalCount)
    {
        $this->setItems($items);
        $this->totalCount = $totalCount;
    }

    /**
     * Just a helper method for short access to AbstractPagination.getTotalItemCount()
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * Renders the pagination
     */
    public function __toString()
    {
        $data = $this->getPaginationData();

        $output = '';
        if (!$this->renderer instanceof Closure) {
            $output = 'add a renderer in order to render a template';
        } else {
            $output = call_user_func($this->renderer, $data);
        }

        return $output;
    }
}