# Ticker

Simple yet very powerful time period based event dispatcher.

## Usage examples

### Callback based

```php
$ticker = new Ticker();

$ticker->addTick(new Tick(Ticker::SECOND, 1, function () {
    // do it every second
}));

$ticker->addTick(new Tick(Ticker::SECOND, 5, function () {
    // do it every 5 seconds
}));

$ticker->addTick(new Tick(Ticker::MINUTE, 1, function() {
    // do it every minute
}));

$ticker->loop();
```

### Simple Tick Object

```php

$tick = new class extends Tick {

    protected $everyN = 1;
    protected $tickType = Ticker::HOUR;

    public function run() 
    {
        // hourly task
    }
}

$ticker = new Ticker();
$ticker->addTick($tick);
$ticker->loop();

```

### Threaded Tick

Threaded Ticks are based on PoolingFactory class since we can't push
the same object Twice on Pool's stack.

```php
class FactoryA extends PoolingFactory {
    public function factorize() {
        return new class extends \Threaded {
            public function run() {
                echo '<';
                sleep(6);
                echo '>';
            }
        };
    }

}

class FactoryB extends PoolingFactory {
    public function factorize() {
        return new class extends \Threaded {
            public function run() {
                echo '#';
            }
        };
    }
}

$ticker = new Ticker();

$ticker->addTick($t1 = new FactoryA(Ticker::SECOND, 1));
$ticker->addTick($t2 = new FactoryB(Ticker::SECOND, 1));

$ticker->loop();

/* outputs: <######><######><######><######><######><######>.. */

```

and shared Pool example:

```php
$pool = new \Pool(3, \Worker::class);

$ticker->addTick($t1 = new FactoryA(Ticker::SECOND, 1, $pool));
$ticker->addTick($t2 = new FactoryB(Ticker::SECOND, 1, $pool));

$ticker->loop();

/* outputs: <#<<>#<>#<>#<>#<>#<>#<>#<>#<>#<>#<>#<>#<>#<>#<>#<.. */
```