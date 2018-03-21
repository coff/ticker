<?php


namespace Coff\Ticker;


interface TickInterface
{
    public function getEveryN();

    public function getTickType();

    public function run();

}