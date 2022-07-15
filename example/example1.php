#!/usr/bin/php
<?php

namespace Coff\Ticker\Examples;

use Coff\Ticker\CallableTick;
use Coff\Ticker\Ticker;
use Coff\Ticker\Time;

include (__DIR__ . '/../vendor/autoload.php');

$ticker = new Ticker();

$ticker->addTick(new CallableTick(Time::SECOND_1000TH, 1, function () {
    /* Consider Ticker has some time of sleep set to some safe level to prevent heating up your CPU although this
       can be also set manually by Ticker::setSleep() / Ticker::setUSleep() */
    echo "o";
}));

$ticker->addTick(new CallableTick(Time::SECOND_100TH, 1, function () {
    /* Consider Ticker has some time of sleep set to some safe level to prevent heating up your CPU although this
       can be also set manually by Ticker::setSleep() / Ticker::setUSleep() */
    echo chr(8) . chr(8) . chr(8) . chr(8) . chr(8) .
         chr(8) . chr(8) . chr(8) . chr(8) . chr(8) . '.';
}));

$ticker->addTick(new CallableTick(Time::SECOND_10TH, 1, function () {
    /* Consider Ticker has some time of sleep set to some safe level to prevent heating up your CPU although this
       can be also set manually by Ticker::setSleep() / Ticker::setUSleep() */
    echo ",";
}));

$i = 0;
$ticker->addTick(new CallableTick(Time::SECOND, 1, function () use (&$i) {
    echo "$i" . PHP_EOL; $i++;
}));

$ticker->addTick(new CallableTick(Time::MINUTE, 1, function() use (&$i) {
    $i=0; echo PHP_EOL;
}));

$ticker->loop();

