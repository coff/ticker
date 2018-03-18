<?php

namespace Coff\Ticker;

class Tick
{

    /** @var string */
    protected $tickType;

    /** @var int */
    protected $everyN = 1;

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

    public function call()
    {
        call_user_func_array($this->callback, $this->params);
    }

    /**
     * @return string
     */
    public function getTickType(): string
    {
        return $this->tickType;
    }

    /**
     * @param string $tickType
     * @return Tick
     */
    public function setTickType(string $tickType): Tick
    {
        $this->tickType = $tickType;
        return $this;
    }

    /**
     * @return int
     */
    public function getEveryN(): int
    {
        return $this->everyN;
    }

    /**
     * @param int $everyN
     * @return Tick
     */
    public function setEveryN(int $everyN): Tick
    {
        $this->everyN = $everyN;
        return $this;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @param callable $callback
     * @return Tick
     */
    public function setCallback(callable $callback): Tick
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return Tick
     */
    public function setParams(array $params): Tick
    {
        $this->params = $params;
        return $this;
    }


}