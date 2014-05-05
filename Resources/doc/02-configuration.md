Configure the bundle
====================

Since version 1.1 the pagination service can make use of the Registry service (from RegistryBundle).

## All configuration options

The configuration options for *app/config/config.yml*:

```yaml
pagination:
    globals:
        template:       'sliding.html.twig' # the default pagination template file name
        auto_register:  true                # if RegistryBundle is available,
                                            # used to load/save pagination cursor
```

### Twig options

The example twig macro (in ``sliding.html.twig``) requires a name for the page parameter
to generate the pagination output. Configure the name (the GET parameter key name) via
the global variable ``pageParameterName`` in *app/config/config.yml*:

```yaml
# Twig Configuration
twig:
    debug:            %kernel.debug%
    strict_variables: %kernel.debug%
    globals:
        pageParameterName:  "page"
```

Hint: This page parameter name is also used by the pagination service `getPageIndex` method.

## That's all

[Return to the index.](index.md)