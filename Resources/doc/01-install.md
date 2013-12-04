Setting up the bundle
=====================

## Install the bundle

First add the bundle to your composer.json file: 

```json
{
    // ...
    "require": {
        // ...
        "jonasarts/pagination-bundle": "1.0.*"
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

You don't need to enable the bundle in the kernel as there is no controller in the bundle present.

### Enable the service

You don't need to enable the bundle in *app/AppKernel.php*. You only need to register the service in *app/config/config.yml*:

```yaml
# Twig Configuration
twig:
    debug:            %kernel.debug%
    strict_variables: %kernel.debug%
    globals:
        pageParameterName:  "page"

services:
    // ...
    pagination_manager:
        class: jonasarts\Bundle\PaginationBundle\PaginationManager
        arguments: [@service_container, @twig]
```

Don't forget to add the global twig variable ``pageParameterName`` which is used by the example ``sliding.html.twig`` template file!

The PaginationManager searches for a default pagination template in the file *app/Resources/views/sliding.html.twig*:

```php
{% set route = app.request.attributes.get('_route') %}
{% set route_params = app.request.query.all %}

{% if pageCount > 1 %}
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