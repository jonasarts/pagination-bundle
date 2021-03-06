<?php

/*
 * This file is part of the Pagination bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\PaginationBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

use jonasarts\Bundle\PaginationBundle\Pagination\Pagination as Paginator;

class Pagination
{
    private $container; // service container
    private $twig; // twig template engine

    private $template = 'sliding.html.twig'; // default pagination template
    private $auto_register = false; // use registry to save/load last values

    /**
     * Constructor
     */
    public function __construct(ContainerInterface $container, \Twig_Environment $twig)
    {
        $this->container = $container;
        $this->twig = $twig;

        // load
        $this->template = $this->container->getParameter('pagination.globals.template'); // default pagination template
        $this->auto_register = $this->container->getParameter('pagination.globals.auto_register'); // use automatic save/load of pagination values
    }

    /**
     * Method handling the auto-registering of values
     * 
     * @param Request $request
     * @param array $data one key=>value set; key is the registry name to lookup if value is null or to update if a value is given
     * @return mixed last value if value is null in data array; current value if value is given in data array
     */
    private function register(Request $request, array $data, $type)
    {
        // $value is null = read / $value is set = write
        list($key, $value) = each($data);

        if ($this->container->has('registry')) {
            $rm = $this->container->get('registry');

            $userid = 0;
            $token = $this->container->get('security.context')->getToken();
            if ($token) {
                $user = $token->getUser();
                if ($user) {
                    $userid = $user->getId();
                }
            }
            $route = $request->get('_route');

            if ($value != null) { 
                $rm->RegistryWrite($userid, 'pagination/'.$route, $key, $type, $value);
            } else { 
                $value = $rm->RegistryRead($userid, 'pagination/'.$route, $key, $type);
            }
        }

        return $value;
    }

    /**
     * Override pagination template on the fly
     * 
     * @param string $template
     * @return Pagination
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Returns a pagination object
     * 
     * @param array $entities
     * @param integer $entity_count
     * @param integer $current_page_index
     * @param integer $page_range
     * @param integer $page_size
     * @param array $additional_data
     * @return Pagination
     */
    public function getPagination($entities, $entity_count, $current_page_index, $page_range, $page_size, $additional_data = null)
    {
        if (($entities != null) && !is_array($entities)) {
            throw new \Exception('Pagination.getPagination: $entities is not an array');
        }
        if ($entities == null) {
            $entities = array();
        }
        if (($additional_data != null) && !is_array($additional_data)) {
            throw new \Exception('Pagination.getPagination: $additional_data is not an array');
        }
        if ($additional_data == null) {
            $additional_data = array();
        }
        
        $pagination = new Paginator($entities, $entity_count);

        $pagination->setPageRange($page_range);
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

    /**
     * @param  Request $request
     * @return string
     */
    public function getPageSizeSelector(Request $request, array $additional_params = null)
    {
        $router = $this->container->get('router');
        $params = array(); //$request->query->all();
        $routename = $request->get('_route');

        if (is_null($additional_params)) $additional_params = array();

        $sizes = array(10,20,50,100);

        $s = '';

        $current = $this->getPageSize($request, 'pagesize'); // read pagesize

        foreach($sizes as $size) {
            $url = $router->generate($routename, array_merge($params, $additional_params, array('pagesize' => $size)));

            if ($current == $size) {
                $s .= sprintf(
                    '<li class="active"><span>%d</span></li>',
                    $size
                    );
            } else {
                $s .= sprintf(
                    '<li><a href="%s" title="%d results per page">%d</a></li>',
                    $url,
                    $size,
                    $size,
                    $size
                    );
            }
        }
        $s = sprintf('<ul class="pagination pagination-sm">%s</ul>', $s);

        return array('pagesizeselector' => $s);
    }

    /**
     * Reads sort field from request.
     * If sort field present, updates value to registry.
     * If no sort field is found, tries to read last value from registry.
     * 
     * @param Request $request
     * @param string $default_field_name
     * @return string
     */
    public function getSortFieldName(Request $request, $default_field_name)
    {
        $sort_field_name = null;
        $sort = $request->query->get('sort');

        $sort_array = explode('.', $sort);
        if (is_array($sort_array)) {
            if (trim($sort_array[0]) != '')
                $sort_field_name = $sort_array[0];
        }

        if ($this->auto_register) {
            $sort_field_name = $this->register($request, array('sortfield' => $sort_field_name), 'str');
        }

        if (trim($sort_field_name) == '') $sort_field_name = $default_field_name;

        return $sort_field_name;
    }

    /**
     * Reads sort direction from request.
     * If sort direction is present, updates value to registry.
     * If no sort direction is found, tries to read last value from registry.
     * 
     * @param Request $request
     * @param string $default_direction
     * @return string
     */
    public function getSortDirection(Request $request, $default_direction = 'asc')
    {
        $sort_direction = null;

        $sort = $request->query->get('sort');
        
        $sort_array = explode('.', $sort);
        if (is_array($sort_array)) {
            if (count($sort_array) > 1)
                $sort_direction = strtolower($sort_array[1]) == 'desc' ? 'desc' : 'asc';
        }
        
        if ($this->auto_register) {
            $sort_direction = $this->register($request, array('sortdirection' => $sort_direction), 'str');
        }

        if (trim($sort_direction) == '') $sort_direction = $default_direction;

        return $sort_direction;
    }

    /**
     * Reads the page index from request.
     * 
     * @param Request $request
     * @param string  $key The parameter name to read from request to get page_index value
     * @param integer $default_page_index The value to return if not custom page_index is found
     * @return interger
     */
    public function getPageIndex(Request $request, $key = 'pageindex', $default_page_index = 1)
    {
        $page_index = null;

        if ($request->query->has($key)) {
            $page_index = $request->query->get($key);
        }

        if ($this->auto_register) {
            $page_index = $this->register($request, array($key => $page_index), 'int');
        }

        if ($page_index == 0 || $page_index == null) $page_index = $default_page_index;

        return $page_index;
    }

    /**
     * Reset (delete) the page index for the current request.
     * 
     * @param Request $request The request is needed to retrieve current route name
     * @param string $key The parameter name to read from request to get page_index value
     * @return Pagination
     */
    public function resetPageIndex(Request $request, $key = 'pageindex')
    {
        if ($this->container->has('registry')) {
            $rm = $this->container->get('registry');

            $userid = 0;
            $token = $this->container->get('security.context')->getToken();
            if ($token) {
                $user = $token->getUser();
                if ($user) {
                    $userid = $user->getId();
                }
            }
            $route = $request->get('_route');

            $value = $rm->RegistryDelete($userid, 'pagination/'.$route, $key, 'int');
        }

        return $this;
    }

    /**
     * Reads the page range (NOT from request, only needed for auto_register behavior).
     * 
     * @param Request $request
     * @param string  $key The parameter name to read from request to get page_range value
     * @param string  $default_page_range The value to return if no custom page_range is found
     * @return integer
     */
    public function getPageRange(Request $request, $key = 'pagerange', $default_page_range = 10)
    {
        $page_range = null;

        if ($request->query->has($key)) {
            $page_index = $request->query->get($key);
        }

        if ($this->auto_register) {
            $page_range = $this->register($request, array($key => $page_range), 'int');
        }

        if ($page_range == 0 || $page_range == null) $page_range = $default_page_range;

        return $page_range;
    }

    /**
     * Reads the page size (NOT from request, only needed for auto_register behavior).
     * 
     * @param Request $request
     * @param string  $key The parameter name to read from request to get page_size value
     * @param integer $default_page_size The value to return if no custom page_size is found
     * @return integer
     */
    public function getPageSize(Request $request, $key = 'pagesize', $default_page_size = 10)
    {
        $page_size = null;

        if ($request->query->has($key)) {
            $page_size = $request->query->get($key);
        }

        if ($this->auto_register) {
            $page_size = $this->register($request, array($key => $page_size), 'int');
        }

        if ($page_size == 0 || $page_size == null) $page_size = $default_page_size;

        return $page_size;
    }
}
