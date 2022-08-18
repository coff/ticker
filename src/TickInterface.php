<?php

namespace Coff\Ticker;

/**
 * Interface TickInterface.
 *
 * Defines all methods proper Tick class should have
 */
interface TickInterface
{
    /**
     * Returns multiplier for tickType.
     *   Examples:
     *      - interval=SECOND, everyN=5 then execute Tick on each fifth second (0, 5, 10, 15, 20, 25, ...)
     **     - interval=MINUTE, everyN=1 then execute Tick on each minute (1,2,3,4,5...)
     *      - interval=HOUR, everyN=6 then execute Tick once in every  6 hours (6, 12, 18, 24).
     */
    public function getEveryN(): int;

    public function getInterval(): Time;

    public function run(): void;
}
