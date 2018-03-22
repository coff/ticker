<?php

namespace Coff\Ticker;

/**
 * Interface TickInterface
 *
 * Defines all methods proper Tick class should have
 *
 * @package Coff\Ticker
 */
interface TickInterface
{
    /**
     * Returns multiplier for tickType.
     *   Examples:
     *      - tickType=SECOND, everyN=5 then execute Tick on each fifth second (0, 5, 10, 15, 20, 25, ...)
     **     - tickType=MINUTE, everyN=1 then execute Tick on each minute (1,2,3,4,5...)
     *      - tickType=HOUR, everyN=6 then execute Tick once in every  6 hours (6, 12, 18, 24)
     * @return int
     */
    public function getEveryN();

    /**
     * Returns tick type. One of Ticker constants: MICROSECOND, SECOND, MINUTE, HOUR, DAY, etc.
     *
     * @return string
     */
    public function getTickType();

    /**
     * @return
     */
    public function run();
}