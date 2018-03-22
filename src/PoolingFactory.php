<?php


namespace Coff\Ticker;


abstract class PoolingFactory implements TickInterface
{
    use TickableTrait;

    /** @var \Pool */
    protected $pool;

    protected $collector;

    public function __construct($tickType, int $everyN)
    {
        $this->setTickType($tickType);
        $this->setEveryN($everyN);
    }

    public function setPool(\Pool $pool) {
        $this->pool = $pool;
        return $this;
    }

    /**
     * @param mixed $collector
     * @return PoolingFactory
     */
    public function setCollector($collector)
    {
        $this->collector = $collector;
        return $this;
    }

    abstract public function factorize();

    public function run()
    {
        $this->pool->submit($this->factorize());
        $this->pool->collect($this->collector);
    }

}