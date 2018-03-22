<?php

namespace Coff\Ticker;

/**
 * Class PoolingFactory
 *
 * Use this class to factorize threads for use with Ticker object.
 *
 * @package Coff\Ticker
 */
abstract class PoolingFactory implements TickInterface
{
    use TickableTrait;

    /** @var \Pool */
    protected $pool;

    /** @var callable */
    protected $collector;

    /**
     * PoolingFactory constructor.
     * @param string $tickType
     * @param int $everyN
     * @param \Pool|null $pool threading pool container, created automatically when given null here
     */
    public function __construct($tickType, $everyN, \Pool $pool = null)
    {
        $this->setTickType($tickType);
        $this->setEveryN($everyN);

        if (null === $pool) {
            $pool = new \Pool(1, \Worker::class);
        }

        $this->setPool($pool);

        /* initialise default collector */
        $this->collector = [$this, 'collect'];
    }

    /**
     * @param \Pool $pool
     * @return $this
     */
    public function setPool(\Pool $pool)
    {
        $this->pool = $pool;
        return $this;
    }

    /**
     * @return \Pool
     */
    public function getPool(): \Pool
    {
        return $this->pool;
    }

    /**
     * @param callable $collector
     * @return $this
     */
    public function setCollector(callable $collector)
    {
        $this->collector = $collector;
        return $this;
    }

    /**
     * Default run method.
     */
    public function run()
    {
        $this->pool->submit($this->factorize());

        /* Default behaviour is to collect() here but more complex solutions may require
         * doing collect() with a separate Tick added to Ticker
         */
        $this->pool->collect($this->collector);
    }

    /**
     * @return \Threaded
     */
    abstract public function factorize();

    /**
     * Collects finished threads for post-run processing
     *
     * Remark: If Pool is shared among several Factories then we can get here not only this Factory's threads!
     *
     * @param \Threaded $garbage
     */
    public function collect(\Threaded $garbage)
    {
        // default method does nothing atm
    }
}