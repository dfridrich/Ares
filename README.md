ARES [![Build Status](https://travis-ci.org/dfridrich/Ares.svg)](https://travis-ci.org/dfridrich/Ares)
====

Communication with ARES (Czech business register).

Installation with [Composer](https://getcomposer.org/)
-----------------------------------------------------

``` sh
php composer require dfridrich/ares
```

Usage
-----

```php
require "../vendor/autoload.php";

use Defr\Ares;

$ares = new Ares();

$record = $ares->findByIdentificationNumber(73263753); // instance of AresRecord

$people = $record->getCompanyPeople(); // array of Person
```
