[![Build Status](https://travis-ci.org/eborges78/CustomI18nRouterBundle.svg?branch=master)](https://travis-ci.org/eborges78/CustomI18nRouterBundle)

# CustomI18nRouterBundle

i18n Bundle for Symfony.

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
    available_locales: ['fr-fr']
    default_locale: 'fr-fr'
    ...
```

Create i18n config files.

For exemple, for a French file, create a file with name "i18n_fr-fr"
 with content like that :

( fr-fr should be into available_locales parameters )


```php
parameters:
    i18n_fr-fr:
        prefix: ""
        name: "france"
        localeUId: 1
        locale: "fr_FR"
        host: "yourdomain.fr"
        routes:
            home: /accueil/
            contact: /contactez-nous/
```



## Requirements

* PHP >= 7.x
* Symfony 2.8
