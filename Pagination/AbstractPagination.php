<?php

/*
 * This file is part of the Pagination bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\PaginationBundle\Pagination;

use Countable;
use Iterator;
use ArrayAccess;

/**
 * Abstract pagination class
 */
abstract class AbstractPagination implements PaginationInterface, Countable, Iterator, ArrayAccess
{
    private $items = array();

    private $currentPageNumber; // use access methods
    private $numItemsPerPage; // use access methods
    
    private $totalCount; // Pagination access

    /**
     * {@inheritDoc}
     * 
     * see PaginationInterface
     */
    public function setCurrentPageNumber($pageNumber)
    {
        $this->currentPageNumber = $pageNumber;
    }
    /**
     * Get currently used page number
     *
     * @return integer
     * 
     * see PaginationInterface
     */
    public function getCurrentPageNumber()
    {
        return $this->currentPageNumber;
    }

    /**
     * {@inheritDoc}
     * 
     * see PaginationInterface
     */
    public function setItemNumberPerPage($numItemsPerPage)
    {
        $this->numItemsPerPage = $numItemsPerPage;
    }
    /**
     * Get number of items per page
     *
     * @return integer
     * 
     * see PaginationInterface
     */
    public function getItemNumberPerPage()
    {
        return $this->numItemsPerPage;
    }

    /**
     * {@inheritDoc}
     * 
     * see PaginationInterface
     */
    public function setTotalItemCount($numTotal)
    {
        $this->totalCount = $numTotal;
    }
    /**
     * Get total item number available
     *
     * @return integer
     * 
     * see PaginationInterface
     */
    public function getTotalItemCount()
    {
        return $this->totalCount;
    }

    /**
     * {@inheritDoc}
     * 
     * see PaginationInterface
     */
    public function setItems($items)
    {
        if (!is_array($items) && !$items instanceof \Traversable) {
            throw new \UnexpectedValueException("Items must be an array type");
        }
        $this->items = $items;
    }
    /**
     * Get current items
     *
     * @return array
     * 
     * see PaginationInterface
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * {@inheritDoc}
     * 
     * see Countable
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * {@inheritDoc}
     * 
     * see Iterator
     */
    public function current() {
        return current($this->items);
    }

    /**
     * {@inheritDoc}
     * 
     * see Iterator
     */
    public function key() {
        return key($this->items);
    }

    /**
     * {@inheritDoc}
     * 
     * see Iterator
     */
    public function next() {
        next($this->items);
    }

    /**
     * {@inheritDoc}
     * 
     * see Iterator
     */
    public function rewind() {
        reset($this->items);
    }

    /**
     * {@inheritDoc}
     * 
     * see Iterator
     */
    public function valid() {
        return key($this->items) !== null;
    }

    /**
     * 
     * see ArrayAccess
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * 
     * see ArrayAccess
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * 
     * see ArrayAccess
     */
    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * 
     * see ArrayAccess
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
}