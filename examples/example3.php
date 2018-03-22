#!/usr/bin/php
<?php

namespace Coff\Ticker\Examples;

use Coff\Ticker\CallableTick;
use Coff\Ticker\Ticker;

include (__DIR__ . '/../vendor/autoload.php');

/* This example presents design flaw for current ticker algorithm. Second Tick should run
 * each 5 seconds, instead it runs every 25 seconds (after 4 cycles of first tick) */

$ticker = new Ticker();

$ticker->addTick(new CallableTick(Ticker::SECOND, 1, function () {
    echo '<';
    sleep(6);
    echo '>';
}));

$ticker->addTick(new CallableTick(Ticker::SECOND, 5, function() {
    echo '#';
}));

$ticker->loop();

