<?php

namespace Coff\Ticker;

/**
 * Trait TickableTrait
 *
 * Make any class 'tickable' with this trait
 *
 * @package Coff\Ticker
 */
trait TickableTrait
{
    /**
     * One of Ticker constants: MICROSECOND, SECOND, MINUTE, HOUR, DAY, etc.
     *
     * @var string
     */
    protected $tickType;

    /**
     * TickType multiplier
     *   Examples:
     *      - tickType=SECOND, everyN=5 then execute Tick on each fifth second (0, 5, 10, 15, 20, 25, ...)
     *      - tickType=MINUTE, everyN=1 then execute Tick on each minute (1,2,3,4,5...)
     *      - tickType=HOUR, everyN=6 then execute Tick once in every  6 hours (6, 12, 18, 24)
     * @var int
     */
    protected $everyN = 1;

    /**
     * @return string
     */
    public function getTickType()
    {
        return $this->tickType;
    }

    /**
     * @param string $tickType
     * @return $this
     */
    public function setTickType($tickType)
    {
        $this->tickType = $tickType;
        return $this;
    }

    /**
     * @return int
     */
    public function getEveryN()
    {
        return $this->everyN;
    }

    /**
     * @param int $everyN
     * @return $this
     */
    public function setEveryN($everyN)
    {
        $this->everyN = $everyN;
        return $this;
    }
}