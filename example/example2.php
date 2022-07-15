#!/usr/bin/php
<?php

namespace Coff\Ticker\Examples;

use Coff\Ticker\Tick;
use Coff\Ticker\Ticker;
use Coff\Ticker\Time;

include (__DIR__ . '/../vendor/autoload.php');

class MyTick extends Tick {

    public function __construct($tickType, $everyN)
    {
        $this
            ->setEveryN($everyN)
            ->setInterval($tickType);
    }

    public function run()
    {
        echo '.';
    }
}

$ticker = new Ticker();

$ticker->addTick(new MyTick(Time::SECOND, 1));

$ticker->loop();

