# CustomI18nRouterBundle

i18n Bundle for Symfony forked from Skores-Group/CustomI18nRouterBundle

Packagist url is : http://54.36.121.135:9080/packages/skores/custom-i18n-router-bundle

## Installation

Download the library using Composer:

```bash
composer require Skores-Group/custom-i18n-router-bundle
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
