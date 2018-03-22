<?php


namespace Coff\Ticker;


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
     * Updates list of active ticks
     */
    protected function updateActiveTicks()
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
                if ($this->sleepLocked || $this->sleep !== null || $this->uSleep !== null) {
                    continue;
                }

                /*
                 * Automatically determine sleep/usleep.
                 */
                switch ($tickType) {
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
     * @param Tick $tick
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
     * @param int $uSleep
     * @return $this
     */
    public function setUSleep(int $uSleep)
    {
        $this->uSleep = $uSleep;
        $this->sleepLocked = true;

        return $this;
    }

    /**
     * @param int $sleep
     * @return $this
     */
    public function setSleep(int $sleep)
    {
        $this->sleep = $sleep;
        $this->sleepLocked = true;

        return $this;
    }

}