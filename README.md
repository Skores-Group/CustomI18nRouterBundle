# CustomI18nRouterBundle

i18n Bundle for Symfony

## Installation

Download the library using Composer:

```bash
composer require eb78/custom-i18n-router-bundle
```

Register Bundle

```php
    ...
    new EB78\CustomI18nRouterBundle\EB78CustomI18nRouterBundle()
    ...
```

Add Parameters

```php
    ...
parameters:
    locale: fr
    available_locales: ['fr-fr', 'pt-pt', 'en-gb']
    default_market: 'fr-fr'
    ...
```

Create i18n config files.

For exemple, for a French file, create a file with name "i18n_fr-fr"
 with content like that :

( fr-fr should be into available_locales parameters )


```php
parameters:
    i18n_fr-fr:
        prefix: "" #optional
        locale: "fr"
        country: "fr"
        host: "www.mywebsite.fr" # optional
        routes:
            homepage: /
            contact: /contactez-nous/
            ...
```



## Requirements

* PHP >= 7.x
* Symfony 2.8
