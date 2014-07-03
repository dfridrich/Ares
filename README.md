# DefrAresBundle

Symfony2 bundle for communication with ARES (Czech business register)

Installation via [Composer](https://getcomposer.org/)
-----------------------------------------------------

Add repository to your composer.json:

``` sh
{
    "require": {
        "dfridrich/defr-ares-bundle": "dev-master"
    }
}
```

Then run
``` sh
php composer.phar update
```

Or directly run

``` sh
php composer.phar require dfridrich/defr-ares-bundle:dev-master
```

Add to AppKernel.php
--------------------

``` php
new Defr\AresBundle\DefrAresBundle()
```

Usage
-----

``` php
$this->get('defr_ares')->...
```

Methods
-------

findByIdentificationNumber($id), findInResById($id) — Find company details

findVatById($id) — Look for czech VAT ID

findByName($name, $city = null)  — Search records based on name (or part) and town (optional)

