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

/**
 * Pagination interface
 */
interface PaginationInterface
{
    /**
     * @param integer $pageNumber
     */
    function setCurrentPageNumber($pageNumber);

    /**
     * @param integer $numItemsPerPage
     */
    function setItemNumberPerPage($numItemsPerPage);

    /**
     * @param integer $numTotal
     */
    function setTotalItemCount($numTotal);

    /**
     * @param mixed $items
     */
    function setItems($items);

    /**
     * @param string $options
     */
    //function setPaginatorOptions($options);

    /**
     * @param array $parameters
     */
    //function setCustomParameters(array $parameters);
}