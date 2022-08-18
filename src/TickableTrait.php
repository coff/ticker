<?php

namespace Coff\Ticker;

/**
 * Trait TickableTrait.
 *
 * Make any class 'tickable' with this trait
 */
trait TickableTrait
{
    protected Time $interval;

    /**
     * TickType multiplier
     *   Examples:
     *      - interval=SECOND, everyN=5 then execute Tick on each fifth second (0, 5, 10, 15, 20, 25, ...)
     *      - interval=MINUTE, everyN=1 then execute Tick on each minute (1,2,3,4,5...)
     *      - interval=HOUR, everyN=6 then execute Tick once in every  6 hours (6, 12, 18, 24).
     */
    protected int $everyN = 1;

    /**
     * @return string
     */
    public function getInterval(): Time
    {
        return $this->interval;
    }

    public function setInterval(Time $interval): self
    {
        $this->interval = $interval;

        return $this;
    }

    public function getEveryN(): int
    {
        return $this->everyN;
    }

    public function setEveryN(int $everyN): self
    {
        $this->everyN = $everyN;

        return $this;
    }
}
