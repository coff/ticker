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

    protected $uSleep=1;
    protected $sleep=0;
    protected $sleepLocked = false;

    /** @var  */
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

    protected $activeTicks;

    private $lasts;
    private $periods = [self::YEAR, self::MONTH, self::WEEK, self::DAY, self::HOUR, self::MINUTE, self::SECOND, self::MICROSECOND];


    public function loop() {

        $timeFormat = implode(',', $this->periods);

        $this->updateActiveTicks();

        $this->lasts = array_combine($this->periods, explode(",", date($timeFormat)));

        while(true) {

            /* \DateTime supports microseconds, date() does not */
            $dateTime = new \DateTime();

            $time = array_combine($this->periods, explode(",", $dateTime->format($timeFormat)));

            foreach ($this->activeTicks as $tickType) {

                /* let's verify if we already called callbacks for current usec, sec, min, hr, ... */
                if ($time[$tickType] !== $this->lasts[$tickType]) {

                    /** @var Tick $tick */
                    foreach ($this->callbacks[$tickType] as $tick) {

                        if ($time[$tickType] % $tick->getEveryN() == 0) {
                            $tick->call();
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

    public function addTick(Tick $tick, $updateActiveTicks = true) {

        $this->callbacks[$tick->getTickType()][] = $tick;

        if ($updateActiveTicks) {
            $this->updateActiveTicks();
        }

        return $this;
    }

    /**
     * @param int $uSleep
     * @return Ticker
     */
    public function setUSleep(int $uSleep): Ticker
    {
        $this->uSleep = $uSleep;
        $this->sleepLocked = true;

        return $this;
    }

    /**
     * @param int $sleep
     * @return Ticker
     */
    public function setSleep(int $sleep): Ticker
    {
        $this->sleep = $sleep;
        $this->sleepLocked = true;

        return $this;
    }

    /**
     * Updates list of active ticks
     */
    protected function updateActiveTicks() {
        $this->activeTicks = [];

        if ($this->sleepLocked === false) {
            $this->uSleep = null; $this->sleep = null;
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

}