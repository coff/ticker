<?php


namespace Coff\Ticker;


class ThreadedTick extends \Threaded implements TickInterface
{
    use TickableTrait;

}