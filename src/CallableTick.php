<?php

namespace Coff\Ticker;

/**
 * Class CallableTick.
 *
 * Use this to define the simplest possible ticks - based on callback functions.
 */
class CallableTick implements TickInterface
{
    use TickableTrait;

    /** @var callable */
    protected $callback;

    /** @var array< */
    protected array $params = [];

    public function __construct(Time $interval, int $everyN, callable $callback = null, array $params = [])
    {
        $this->interval = $interval;
        $this->everyN = $everyN;
        $this->callback = $callback;
        $this->params = $params;
    }

    public function run(): void
    {
        call_user_func_array($this->callback, $this->params);
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function setCallback(callable $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }
}
