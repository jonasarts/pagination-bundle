Setting up the bundle
=====================

## Install the bundle

First add the bundle to your composer.json file: 

```json
{
    // ...
    "require": {
        // ...
        "jonasarts/pagination-bundle": "1.1.*"
    },
    "minimum-stability": "stable",
    // ...
}
```

Then run composer.phar:

``` bash
$ php composer.phar install
```

## Enable the bundle

Register the bundle in the kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new jonasarts\Bundle\PaginationBundle\PaginationBundle(),
    );
}
```

## Configuration options

[Read the bundle configuration options](02-configuration.md)

## sliding.html.twig Example

The Pagination service searches for a default pagination template in the file *app/Resources/views/sliding.html.twig*:

```php
{% set route = app.request.attributes.get('_route') %}
{% set route_params = app.request.query.all %}

{% if pageCount > 0 %}
<div class="pagination pagination-small">
<ul>
    {# if first is defined and current != first %}
        <li>
            <a href="{{ path(route, route_params|merge({(pageParameterName): first})) }}">&lt;&lt;</a>
        </li>
    {% endif #}

    {% if previous is defined %}
        <li><a href="{{ path(route, route_params|merge({(pageParameterName): previous})) }}">&laquo;</a></li>
    {% else %}
        <li class="disabled"><span>&laquo;</span></li>
    {% endif %}

    {% for page in pagesInRange %}
        {% if page != current %}
            <li>
                <a href="{{ path(route, route_params|merge({(pageParameterName): page})) }}">{{ page }}</a>
            </li>
        {% else %}
            <li class="active"><span>{{ page }}</span></li>
        {% endif %}

    {% endfor %}

    {% if next is defined %}
        <li><a href="{{ path(route, route_params|merge({(pageParameterName): next})) }}">&raquo;</a></li>
    {% else %}
        <li class="disabled"><span>&raquo;</span></li>
    {% endif %}

    {# if last is defined and current != last %}
        <li>
            <a href="{{ path(route, route_params|merge({(pageParameterName): last})) }}">&gt;&gt;</a>
        </li>
    {% endif #}
</ul>
</div>
{% endif %}
```

## That's it

Check out the docs for information on how to use the bundle! [Return to the index.](index.md)