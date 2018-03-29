<?php

namespace Coff\Ticker;

/**
 * Class Ticker
 * @package Coff\Ticker
 */
class Ticker
{
    const
        YEAR = 'y',
        MONTH = 'n',
        WEEK = 'W',
        DAY = 'z',
        HOUR = 'G',
        MINUTE = 'i',
        SECOND = 's',
        SECOND_10TH = 's/10',
        SECOND_100TH = 's/100',
        SECOND_1000TH = 's/1000',
        SECOND_10000TH = 's/10000',
        MICROSECOND = 'u';

    /** @var int $uSleep sleep time in microseconds */
    protected $uSleep = 0;

    /** @var int $sleep sleep time in seconds */
    protected $sleep = 0;

    /** @var bool $sleepLocked whether sleepTime is set manually (normally is determined automatically upon Ticks' periods */
    protected $sleepLocked = false;

    /** @var */
    protected $callbacks = [
        self::MICROSECOND => [],
        self::SECOND_10000TH => [],
        self::SECOND_1000TH => [],
        self::SECOND_100TH => [],
        self::SECOND_10TH => [],
        self::SECOND => [],
        self::MINUTE => [],
        self::HOUR => [],
        self::DAY => [],
        self::WEEK => [],
        self::MONTH => [],
        self::YEAR => [],
    ];

    /** @var array $activeTicks keeps periods that have Ticks added */
    protected $activeTicks;

    /** @var array $lasts keeps last calls for each period */
    private $lasts;

    /** @var array $periods */
    private $periods = [
        self::YEAR,
        self::MONTH,
        self::WEEK,
        self::DAY,
        self::HOUR,
        self::MINUTE,
        self::SECOND,
        self::MICROSECOND,
    ];


    /**
     * Main ticker loop. Call it to execute Ticker.
     */
    public function loop()
    {

        $timeFormat = implode(',', $this->periods);

        $this->updateActiveTicks();

        $this->lasts = array_combine($this->periods, explode(",", date($timeFormat)));

        /* main loop */
        while (true) {

            /* \DateTime supports microseconds, date() does not */
            $dateTime = new \DateTime();

            $time = array_combine($this->periods, explode(",", $dateTime->format($timeFormat)));

            $time[self::SECOND_10TH] = substr($time[self::MICROSECOND],0,1);
            $time[self::SECOND_100TH] = substr($time[self::MICROSECOND],0,2);
            $time[self::SECOND_1000TH] = substr($time[self::MICROSECOND],0,3);
            $time[self::SECOND_10000TH] = substr($time[self::MICROSECOND],0,4);

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
    public function updateActiveTicks()
    {
        $this->activeTicks = [];

        if ($this->sleepLocked === false) {
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
                    case self::SECOND_10000TH:
                        $this->uSleep = 10; // 1/10000th of a second
                        $this->sleep = 0;
                        break;
                    case self::SECOND_1000TH:
                        $this->uSleep = 100; // 1/10000th of a second
                        $this->sleep = 0;
                        break;
                    case self::SECOND_100TH:
                        $this->uSleep = 1000; // 1/1000th of a second
                        $this->sleep = 0;
                        break;
                    case self::SECOND_10TH:
                        $this->uSleep = 10000; // 1/100th of a second
                        $this->sleep = 0;
                        break;
                    case self::SECOND:
                        $this->uSleep = 100000; // 1/10th of a second
                        $this->sleep = 0;
                        break;
                    case self::MINUTE:
                        $this->uSleep = 0;
                        $this->sleep = 1; // one second of sleep
                        break;
                    case self::HOUR:
                        $this->uSleep = 0;
                        $this->sleep = 60; // one minute of sleep
                        break;
                    case self::DAY:
                        $this->uSleep = 0;
                        $this->sleep = 60 * 60; // one hour of sleep
                        break;
                    case self::MONTH:
                        // no break
                    case self::YEAR:
                        // no break
                    case self::WEEK:
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
     * Adds tick definition
     * @param TickInterface $tick
     * @param bool $updateActiveTicks
     * @return $this
     */
    public function addTick(TickInterface $tick, $updateActiveTicks = true)
    {

        $this->callbacks[$tick->getTickType()][] = $tick;

        if ($updateActiveTicks) {
            $this->updateActiveTicks();
        }

        return $this;
    }

    /**
     * Sets uSleep time manually - value that ticker can't overwrite (unless given null here) with its automatically
     * determined value.
     *
     * @param int|null $uSleep
     * @return $this
     */
    public function setUSleep(int $uSleep = null)
    {
        $this->uSleep = $uSleep;

        if ($uSleep === null) {
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
     * @param int|null $sleep
     * @return $this
     */
    public function setSleep(int $sleep = null)
    {
        $this->sleep = $sleep;

        if ($sleep === null) {
            $this->sleepLocked = false;
        } else {
            $this->sleepLocked = true;
        }


        return $this;
    }

}