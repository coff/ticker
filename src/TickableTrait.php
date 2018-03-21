<?php


namespace Coff\Ticker;


trait TickableTrait
{
    /** @var string */
    protected $tickType;

    /** @var int */
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
    public function setTickType(string $tickType)
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
    public function setEveryN(int $everyN)
    {
        $this->everyN = $everyN;
        return $this;
    }
}