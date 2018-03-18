# Ticker

Simple yet very powerful time period based event dispatcher.

Usage example:

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