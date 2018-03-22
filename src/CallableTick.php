<?php

namespace Coff\Ticker;

/**
 * Class CallableTick
 *
 * Use this to define simplest possible ticks - based on callback functions.
 *
 * @package Coff\Ticker
 */
class CallableTick implements TickInterface
{
    use TickableTrait;

    /** @var callable */
    protected $callback;

    /** @var array */
    protected $params = [];

    /**
     * Tick constructor.
     * @param $tickType
     * @param $everyN
     * @param callable|null $callback
     * @param array|[] $params
     */
    public function __construct($tickType, $everyN, callable $callback = null, array $params = [])
    {
        $this->tickType = $tickType;
        $this->everyN = $everyN;
        $this->callback = $callback;
        $this->params = $params;
    }

    /**
     * Run calling a callback
     */
    public function run()
    {
        call_user_func_array($this->callback, $this->params);
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }


}