<?php

namespace Coff\Ticker;

use Pool;
use Threaded;
use Worker;

/**
 * Class PoolingFactory.
 *
 * Use this class to factorize threads for use with Ticker object.
 */
abstract class PoolingFactory implements TickInterface
{
    use TickableTrait;

    /** @var Pool */
    protected $pool;

    /** @var callable */
    protected $collector;

    /**
     * PoolingFactory constructor.
     *
     * @param string    $interval
     * @param int       $everyN
     * @param Pool|null $pool     threading pool container, created automatically when given null here
     */
    public function __construct(Time $interval, $everyN, Pool $pool = null)
    {
        $this->setInterval($interval);
        $this->setEveryN($everyN);

        if (null === $pool) {
            $pool = new Pool(1, Worker::class);
        }

        $this->setPool($pool);

        /* initialise default collector */
        $this->collector = [$this, 'collect'];
    }

    /**
     * @return $this
     */
    public function setPool(Pool $pool)
    {
        $this->pool = $pool;

        return $this;
    }

    public function getPool(): Pool
    {
        return $this->pool;
    }

    /**
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
    public function run(): void
    {
        $this->pool->submit($this->factorize());

        /* Default behaviour is to collect() here but more complex solutions may require
         * doing collect() with a separate Tick added to Ticker
         */
        $this->pool->collect($this->collector);
    }

    /**
     * @return Threaded
     */
    abstract public function factorize();

    /**
     * Collects finished threads for post-run processing.
     *
     * Remark: If Pool is shared among several Factories then we can get here not only this Factory's threads!
     */
    public function collect(Threaded $garbage): void
    {
        // default method does nothing atm
    }
}
