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

use Symfony\Component\HttpFoundation\Request;

class PaginationManager
{
    private $container;
    private $twig;

    private $template = 'sliding.html.twig';

    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container, \Twig_Environment $twig)
    {
        $this->container = $container;
        $this->twig = $twig;
    }

    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    public function getPagination($entities, $entity_count, $current_page_index, $page_size, $additional_data = null)
    {
        if (($entities != null) && !is_array($entities)) {
            throw new \Exception('PaginationManager.getPagination: $entities is not an array');
        }
        if ($entities == null) {
            $entities = array();
        }
        if (($additional_data != null) && !is_array($additional_data)) {
            throw new \Exception('PaginationManager.getPagination: $additional_data is not an array');
        }
        if ($additional_data == null) {
            $additional_data = array();
        }
        
        $pagination = new Pagination($entities, $entity_count);

        $pagination->setPageRange($page_size);
        $pagination->setItemNumberPerPage($page_size);
        $pagination->setCurrentPageNumber($current_page_index);

        $twig_env = $this->twig;
        $twig_template = $this->template;

        $pagination->renderer = function($data) use ($twig_env, $twig_template, $additional_data) {
            //return var_export($data, true);
            // common errors to check: is $twig_template file present?
            //return $twig_template;
            try {
                return $twig_env->render($twig_template, array_merge($data, $additional_data));
            } catch(\Exception $e) {
                return $e->getMessage();
            }
        };

        return $pagination;
    }

    public function getSortFieldName(Request $request, $default_field_name)
    {
        $sort = $request->query->get('sort');
        $sort_field_name = $default_field_name;

        $sort_array = explode(".", $sort);
        if (is_array($sort_array)) {
            if (trim($sort_array[0]) != '')
                $sort_field_name = $sort_array[0];
        }

        return $sort_field_name;
    }

    public function getSortDirection(Request $request, $default_direction = 'asc')
    {
        $sort = $request->query->get('sort');
        $sort_direction = $default_direction;
        
        $sort_array = explode(".", $sort);
        if (is_array($sort_array)) {
            if (count($sort_array) > 1)
                $sort_direction = strtolower($sort_array[1]) == 'desc' ? 'desc' : 'asc';
        }

        return $sort_direction;
    }
}