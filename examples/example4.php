#!/usr/local/bin/php
<?php

namespace Coff\Ticker\Examples;

use Coff\Ticker\PoolingFactory;
use Coff\Ticker\Ticker;

include (__DIR__ . '/../vendor/autoload.php');

/*
 * This example shows usage of Ticker with threading (PHP 7.2+)
 * Introducing two factories that factorize Threaded objects as ticks.
 * Each object has its own Pool assigned but they can also share one pool.
 * This case runs similar tasks as example 3 but due to threading
 * now tasks aren't skipped! When running this example you can clearly see
 * that #-task is executed several times while <-> task still perfoms its
 * single execution.
 *
 * See http://php.net/manual/pl/book.pthreads.php for help on threading.
 *
 */

class FactoryA extends PoolingFactory
{
    public function factorize()
    {
        return new class extends \Threaded {
            public function run()
            {
                echo '<';
                sleep(6);
                echo '>';
            }
        };
    }

}

class FactoryB extends PoolingFactory
{
    public function factorize()
    {
        return new class extends \Threaded {
            public function run()
            {
                echo '#';
            }
        };
    }
}

$ticker = new Ticker();

$ticker->addTick($t1 = new FactoryA(Ticker::SECOND, 1));
$ticker->addTick($t2 = new FactoryB(Ticker::SECOND, 1));

$t1
    ->setPool(new \Pool(1, \Worker::class))
    ->setCollector(function(\Threaded $garbage) { });
$t2
    ->setPool(new \Pool(1, \Worker::class))
    ->setCollector(function(\Threaded $garbage) { });


$ticker->loop();
