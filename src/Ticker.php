<?php

namespace Coff\Ticker;

use DateTime;

/**
 * Class Ticker.
 */
class Ticker
{
    protected ?int $uSleep = 0;

    protected ?int $sleep = 0;

    /** @var bool whether sleepTime is set manually (normally is determined automatically upon Ticks' periods */
    protected bool $sleepLocked = false;

    protected array $callbacks;

    protected array $activeTicks;

    private array $lasts;

    private array $periods;

    private string $timeFormat;

    public function __construct()
    {
        /*
         * a bit dirty workaround since expressions are not allowed outside class methods and
         * PHP Enums are not allowed as array keys
         */
        $this->callbacks = [
            Time::MICROSECOND->value => [],
            Time::SECOND_10000TH->value => [],
            Time::SECOND_1000TH->value => [],
            Time::SECOND_100TH->value => [],
            Time::SECOND_10TH->value => [],
            Time::SECOND->value => [],
            Time::MINUTE->value => [],
            Time::HOUR->value => [],
            Time::DAY->value => [],
            Time::WEEK->value => [],
            Time::MONTH->value => [],
            Time::YEAR->value => [],
        ];

        $this->periods = [
            Time::YEAR->value,
            Time::MONTH->value,
            Time::WEEK->value,
            Time::DAY->value,
            Time::HOUR->value,
            Time::MINUTE->value,
            Time::SECOND->value,
            Time::MICROSECOND->value,
        ];

        $this->timeFormat = implode(',', $this->periods);
    }

    private function splitTime(DateTime $dateTime): array
    {
        $time = explode(',', $dateTime->format($this->timeFormat));
        $arr = array_combine($this->periods, $time);

        $arr[Time::SECOND_10TH->value] = substr($arr[Time::MICROSECOND->value], 0, 1);
        $arr[Time::SECOND_100TH->value] = substr($arr[Time::MICROSECOND->value], 0, 2);
        $arr[Time::SECOND_1000TH->value] = substr($arr[Time::MICROSECOND->value], 0, 3);
        $arr[Time::SECOND_10000TH->value] = substr($arr[Time::MICROSECOND->value], 0, 4);

        return $arr;
    }

    /**
     * Main ticker loop. Call it to execute Ticker.
     */
    public function loop(DateTime $until = null): void
    {
        $this->updateActiveTicks();

        $this->lasts = $this->splitTime(new DateTime());

        /* main loop */
        while (true) {
            /* \DateTime supports microseconds, date() does not */
            $dateTime = new DateTime();

            if (null !== $until && $dateTime > $until) {
                break;
            }

            $time = $this->splitTime($dateTime);

            foreach ($this->activeTicks as $tickType) {
                /* let's verify if we already called callbacks for current usec, sec, min, hr, ... */
                if ($time[$tickType] !== $this->lasts[$tickType]) {
                    /** @var Tick $tick */
                    foreach ($this->callbacks[$tickType] as $tick) {
                        /*  Remark:
                         *    A flaw of this kind of design is that some ticks
                         *    may get skipped if other tasks take too much time.
                         *    Risk is greater when everyN > 1
                         */
                        if ($time[$tickType] % $tick->getEveryN() == 0) {
                            $tick->run();
                        }
                    }

                    $this->lasts[$tickType] = $time[$tickType];
                }
            }

            /* let's have a proper amount of sleep */
            usleep($this->uSleep);
            sleep($this->sleep);
        }
    }

    /**
     * Updates list of active ticks. Automatically performed upon each addTick() call.
     */
    public function updateActiveTicks(): void
    {
        $this->activeTicks = [];

        if (false === $this->sleepLocked) {
            $this->uSleep = null;
            $this->sleep = null;
        }

        foreach ($this->callbacks as $tickType => $callbacks) {
            if ($callbacks) {
                $this->activeTicks[] = $tickType;

                /* continue if sleep time is locked or already estabilished */
                if (true === $this->sleepLocked) {
                    continue;
                }

                /* smallest time callback goes first and decide */
                if (false === is_null($this->sleep)) {
                    continue;
                }
                /*
                 * Automatically determine sleep/usleep.
                 */
                switch ($tickType) {
                    case Time::SECOND_10000TH:
                        $this->uSleep = 10; // 1/10000th of a second
                        $this->sleep = 0;
                        break;
                    case Time::SECOND_1000TH:
                        $this->uSleep = 100; // 1/10000th of a second
                        $this->sleep = 0;
                        break;
                    case Time::SECOND_100TH:
                        $this->uSleep = 1000; // 1/1000th of a second
                        $this->sleep = 0;
                        break;
                    case Time::SECOND_10TH:
                        $this->uSleep = 10000; // 1/100th of a second
                        $this->sleep = 0;
                        break;
                    case Time::SECOND:
                        $this->uSleep = 100000; // 1/10th of a second
                        $this->sleep = 0;
                        break;
                    case Time::MINUTE:
                        $this->uSleep = 0;
                        $this->sleep = 1; // one second of sleep
                        break;
                    case Time::HOUR:
                        $this->uSleep = 0;
                        $this->sleep = 60; // one minute of sleep
                        break;
                    case Time::DAY:
                        $this->uSleep = 0;
                        $this->sleep = 60 * 60; // one hour of sleep
                        break;
                    case Time::MONTH:
                    case Time::YEAR:
                    case Time::WEEK:
                        /* one tick per day - seems like a program in coma ;) */
                        $this->uSleep = 0;
                        $this->sleep = 60 * 60 * 24; // one day of sleep
                        break;
                    default:
                        /* just enough to prevent CPU heating up yet this should be set manually then */
                        $this->uSleep = 50;
                        $this->sleep = 0;
                }
            }
        }
    }

    /**
     * Adds tick definition.
     *
     * @param bool $updateActiveTicks
     *
     * @return $this
     */
    public function addTick(TickInterface $tick, $updateActiveTicks = true)
    {
        $this->callbacks[$tick->getInterval()->value][] = $tick;

        if ($updateActiveTicks) {
            $this->updateActiveTicks();
        }

        return $this;
    }

    /**
     * Sets uSleep time manually - value that ticker can't overwrite (unless given null here) with its automatically
     * determined value.
     *
     * @return $this
     */
    public function setUSleep(int $uSleep = null)
    {
        $this->uSleep = $uSleep;

        if (null === $uSleep) {
            $this->sleepLocked = false;
        } else {
            $this->sleepLocked = true;
        }

        return $this;
    }

    /**
     * Sets uSleep time manually - value that ticker can't overwrite (unless given null here) with its automatically
     * determined value.
     *
     * @return $this
     */
    public function setSleep(int $sleep = null)
    {
        $this->sleep = $sleep;

        if (null === $sleep) {
            $this->sleepLocked = false;
        } else {
            $this->sleepLocked = true;
        }

        return $this;
    }
}
