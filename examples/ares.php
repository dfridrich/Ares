<?php

require __DIR__.'/../vendor/autoload.php';

use Defr\Ares;

$ares = new Ares();

$record = $ares->findByIdentificationNumber(73263753);

echo sprintf(
    '%s, %s, %s, %s, IČ %s, DIČ %s',
    $record->getCompanyName(),
    $record->getStreet(),
    $record->getZip(),
    $record->getTown(),
    $record->getCompanyId(),
    $record->getTaxId()
);
