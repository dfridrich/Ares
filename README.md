ARES 
====

[![Build Status](https://img.shields.io/travis/dfridrich/Ares.svg?style=flat-square)](https://travis-ci.org/dfridrich/Ares)
[![Quality Score](https://img.shields.io/scrutinizer/g/dfridrich/Ares.svg?style=flat-square)](https://scrutinizer-ci.com/g/dfridrich/Ares)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/dfridrich/Ares.svg?style=flat-square)](https://scrutinizer-ci.com/g/dfridrich/Ares)
[![Downloads](https://img.shields.io/packagist/dt/dfridrich/ares.svg?style=flat-square)](https://packagist.org/packages/dfridrich/ares)
[![Latest stable](https://img.shields.io/packagist/v/dfridrich/ares.svg?style=flat-square)](https://packagist.org/packages/dfridrich/ares)


Communication with ARES & Justice (Czech business registers).

Installation with [Composer](https://getcomposer.org/)
-----------------------------------------------------

``` sh
php composer require dfridrich/ares
```

Usage
-----

```php
require __DIR__.'/vendor/autoload.php';

use Defr\Ares;

$ares = new Ares();

$record = $ares->findByIdentificationNumber(73263753); // instance of AresRecord

$people = $record->getCompanyPeople(); // array of Person
```
