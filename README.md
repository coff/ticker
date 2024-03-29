# Ticker

Simple yet powerful time period based event dispatcher.

## Features

- lightweight algorithm - see ```Ticker::loop()```
- open architecture - see: ```TickInterface```, ```TickableTrait```
- automatically determined main loop's sleep time (based upon defined Ticks) to save on processor usage - see: ```Ticker::updateActiveTicks()```
- threading supported for PHP 7.2+ - see: http://php.net/manual/en/book.pthreads.php
- several ways of defining task to be executed:
  - callbacks
  - trait based
  - extending class based
  - through factory (for threading)

## Usage examples

### Callback based

```php
$ticker = new Ticker();

$ticker->addTick(new Tick(Time::SECOND, 1, function () {
    // do it every second
}));

$ticker->addTick(new Tick(Time::SECOND, 5, function () {
    // do it every 5 seconds
}));

$ticker->addTick(new Tick(Time::MINUTE, 1, function() {
    // do it every minute
}));

$ticker->loop();
```

### Extending class based

```php

$tick = new class extends Tick {

    protected $everyN = 1;
    protected $interval = Time::HOUR;

    public function run() 
    {
        // hourly task
    }
}

$ticker = new Ticker();
$ticker->addTick($tick);
$ticker->loop();

```
