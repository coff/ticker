#!/usr/bin/php
<?php

namespace Coff\Ticker\Examples;

use Coff\Ticker\Tick;
use Coff\Ticker\Ticker;

include (__DIR__ . '/../vendor/autoload.php');

$ticker = new Ticker();

$ticker->addTick(new Tick(Ticker::MICROSECOND, 1000, function () {
    /* Consider Ticker has some time of sleep set to some safe level to prevent heating up your CPU although this
       can be also set manually by Ticker::setSleep() / Ticker::setUSleep() */
    echo ".";
}));

$i = 1;
$ticker->addTick(new Tick(Ticker::SECOND, 1, function () use (&$i) {
    echo "$i"; $i++;
}));

$ticker->addTick(new Tick(Ticker::MINUTE, 1, function() use (&$i) {
    $i=1; echo PHP_EOL;
}));

$ticker->loop();

