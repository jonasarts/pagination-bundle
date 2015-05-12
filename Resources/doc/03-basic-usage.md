Using the bundle
================

The Pagination (aka PaginationManager) is a service to handle all pagination related operations.
This includes some helper methods for sorting and loading/saving a cursor.

The method ``Pagination.getPagination()`` will return a pagination object with following properties/methods:
- pageCount
- pagesInRange
- totalCount
- first
- previous
- current
- next
- last

To render a pagination, just output the pagination object returned by the Pagination service.

```php
    $pm = $this->get('pagination');

    $objects = array(); // an object array
    $count = 0; // total count of objects (NOT all object must be present in the object array)
    $page_index = 1; // first page
    $page_range = 5; // show 5 pages in paginator navigation
    $page_size = 10; // 10 objects per page

    $pagination = $pm->getPagination($objects, $count, $page_index, $page_range, $page_size);

    echo $pagination; // or echo $pagination->__toString();
```

To register a different pagination twig template:

```php
    $pm = $this->get('pagination');
    $pm->setTemplate('my/custom/path/to/paginationTwigTemplate.html.twig');

    // ...
```

This is how a controller action using a pagination manager may look like:

```php
    /**
     * @Route("/", name="index")
     * @Template()
     */
    public function indexAction()
    {
        $pm = $this->get('pagination'); // the pagination service
        $request = $this->getRequest(); // the current request
        
        $record_count = 0;
        $records = null;
        
        $page_range = 10; // how many pages will be displayed in the paginator
        $page_size = 10; // how many records will be displayed per page
        
        // calculate sort
        $sort_field_name = $pm->getSortFieldName($request, 'id'); // read the sortfieldname from the request, default fallback value is 'id'
        $sort_direction = $pm->getSortDirection($request, 'asc'); // read the sortdireciton from the request, defalt fallback value is 'asc'
        
        // calculate limit
        $page_index = $pm->getPageIndex($request, 'page'); // read the page offset from the request using the parameter `page`, default fallback value will be 1
        $page_range = $pm->getPageRange($request, 'pagerange', $page_range); // read the page range from registry, if RegistryBundle is available
        $page_size = $pm->getPageSize($request, 'pagesize', $page_size); // read the page size from registry, if RegistryBundle is available

        // query
        $record_count = ... retrieve the total count of records as an integer value
        $records = ... retrieve the record objects limited by sort and page as an object array

        // pagination
        $pagination = $pm->getPagination($records, $record_count, $page_index, $page_range, $page_size);

        return array('pagination' => $pagination);
    }
```

A corresponding twig template to output the pagination:

```twig
{{ pagination|raw }}
```

A full featured example
=======================

This example shows you how to use the pagination manger / paginator to include not only pagination but also the one-column sorting behavior.

Pretend that an entity ``RegistryKey`` with the properties ``name`` and ``value`` is present in a Bundle named ``RKBundle``.

The controller code:

```php
class RegistryKeyController extends Controller
{
    // ...

    /**
     * Count all objects
     */
    private function getRKCount()
    {
        $em = $this->getDoctrine()->getManager();
        
        $query = $em
            ->createQueryBuilder()
            ->select('count(rk.id)')
            ->from('RKBundle:RegistryKey', 'rk')
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * Return all objects paginated
     */
    private function getRK($sort_field_name, $sort_direction, $current_page_index, $page_size)
    {
        $em = $this->getDoctrine()->getManager();
        
        $page_offset = ($current_page_index - 1) * $page_size; // note this page index to offset calculation
        $page_limit = $page_size;

        $query = $em
            ->createQueryBuilder()
            ->select('rk')
            ->from('RKBundle:RegistryKey', 'rk')
            ->orderBy('rk.'.$sort_field_name, $sort_direction)
            ->setFirstResult($page_offset)
            ->setMaxResults($page_limit)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @Route("/", name="rk_index")
     * @Template()
     */
    public function indexAction()
    {
        $pm = $this->get('pagination');
        $request = $this->getRequest();

        $rk_count = 0;
        $rks = null;

        $page_range = 10;
        $page_size = 10;

        // calculate sort
        $sort_field_name = $pm->getSortFieldName($request, 'id');
        $sort_direction = $pm->getSortDirection($request, 'asc');

        // calculate limit
        $page_index = $pm->getPageIndex($request, 'page');
        $page_range = $pm->getPageRange($request, 'pagerange', $page_range);
        $page_size = $pm->getPageSize($request, 'pagesize', $page_size);

        // query
        $rk_count = $this->getRKCount();
        $rks = $this->getRK($sort_field_name, $sort_direction, $page_index, $page_size);

        // pagination
        $pagination = $pm->getPagination($rks, $rk_count, $page_index, $page_range, $page_size);

        return array('sort_field_name' => $sort_field_name, 'sort_direction' => $sort_direction, 'pagination' => $pagination);
    }

    // ...

    // here you would have the other routes for rk_new, rk_edit and rk_delete
}
```

The column sort link generator macro in the file *macro.html.twig*:

```twig
{% macro column(current_sort_field_name, current_sort_direction, field_name, caption) %}
{% spaceless %}
    {% set sort_field = (current_sort_field_name == field_name and current_sort_direction == 'asc') ? field_name ~ '.desc' : field_name ~ '.asc' %}

    {% if current_sort_field_name == field_name %}
        {% set sort_field_caret = (sort_field == field_name ~ '.asc') ? '&nbsp;<i class="fa fa-caret-down"></i>' : '&nbsp;<i class="fa fa-caret-up"></i>' %}
    {% endif %}

    {% set route = app.request.attributes.get('_route') %}
    {% set route_params = app.request.query.all %}

    <a href="{{ path(route, route_params|merge({ 'sort': sort_field })) }}">{{ caption }}{{ sort_field_caret|default('')|raw }}</a>
{% endspaceless %}
{% endmacro %}
```

The twig template *index.html.twig*:
```twig
{% extends('base.html.twig') %}
{% import 'macro.html.twig' as sorts %}

{% block content%}
<!-- the header (includes the add button) -->

<h1>All objects <span class="badge badge-info">{{ pagination.totalcount }}</span></h1>

<!-- the table with the object data rows -->
<p><table class="table table-hover table-condensed">
<tr>
    <th>id</th><th>{{ sorts.column(sort_field_name, sort_direction, 'name', 'Name') }}</th><th>{{ sorts.column(sort_field_name, sort_direction, 'value', 'Value') }}</th><th><a href="{{ path('rk_new') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add</a></th>
{% for rk in pagination %}
    <tr>
        <td>{{ rk.id }}</td><td>{{ rk.name }}</td><td>{{ rk.value }}</td>
        <td>
            <div class="pull-right">
                <a href="{{ path('rk_edit', { 'id': rk.id }) }}" class="btn"><i class="fa fa-pencil"></i> edit</a>
                <button class="btn" onclick="$('#modal_rk_{{ rk.id }}').modal()"><i class="fa fa-trash-o"></i> delete</button>
            </div>
        </td>
    </tr>
{% endfor %}
</table></p>

<!-- the pagination -->
{{ pagination|raw }}

<!-- bootstrap modal dialogs for object deletion -->
{% for rk in pagination %}
<div id="modal_rk_{{ rk.id }}" class="modal fade" role="dialog" aria-labelledby="modal_rk_label_{{ rk.id }}" aria-hidden="true">
    <div class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="modal_rk_label_{{ rk.id }}">Confirm object deletion</h4>
                </div>
                <div class="modal-body">
                    <p>Delete object {{ rk.name }}?</p>
                </div>
                <div class="modal-footer">
                    <a href="{{ path('rk_delete', { 'id': rk.id }) }}" class="btn btn-danger">Delete</a>
                    <button class="btn" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
{% endfor %}
{% endblock %}
```

Using the PageSizeSelector
==========================

To generate a pagesizeselector, just call the method on the Pagination service:

```php
    /**
     * @Route("/", name="index")
     * @Template()
     */
    public function indexAction()
    {
        $pm = $this->get('pagination');
        $request = $this->getRequest();

        $pagination = $pm->getPagination(...);

        $pagesizeselector = $pm->getPageSizeSelector($request, array('param' => 'value');

        return array('pagination' => $pagination, 'pagesizeselector' => $pagesizeselector);
    }
```

A corresponding twig template to output the pagesizeselector:

```twig
{{ pagesizeselector|raw }}
```

[Return to the index.](index.md)
