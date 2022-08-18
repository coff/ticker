<?php

namespace Coff\Ticker\Test;

use Coff\Ticker\CallableTick;
use Coff\Ticker\Ticker;
use Coff\Ticker\Time;
use PHPUnit\Framework\TestCase;

class Tick
{
    public function run(): void
    {
    }
}

/* for cases with high density of calls in small period of time we set our expectancy a bit lower;
   this should still be considered as acceptable as the goal was to be able to run code with some desired more or less accurate
   frequency not to run it N times in any amount of time */

class TickerTest extends TestCase
{
    public function loopDataProvider(): array
    {
        return [
            ['+1 sec', [Time::SECOND_10TH, 1, 10]],     // ten calls during 1 sec, one call each 100ms
            ['+1 sec', [Time::SECOND_100TH, 1, 90]],   // a ~hundred calls during 1 sec, one call each 10ms
            ['+1 sec', [Time::SECOND_100TH, 10, 10]],   // ten calls during 1 sec, one call each 100ms
            ['+1 sec', [Time::SECOND_1000TH, 10, 90]], // a ~hundred calls during 1 sec, one call each 10ms
            ['+1 sec', [Time::SECOND_1000TH, 2, 450]],  // ~ five hundred calls during 1 sec, one call each 2ms
            ['+2 sec', [Time::SECOND, 1, 2]],           // two calls during 2 sec, one call each 1s
            ['+2 sec', [Time::SECOND, 2, 1]],           // one call during 2 sec, one call each 2s
        ];
    }

    /** @dataProvider loopDataProvider */
    public function testLoopSingleTick(string $checkAfter, $tickOne): void
    {
        [$interval, $everyN, $expectedCalls] = $tickOne;
        $mock = $this->createMock(Tick::class);
        $mock->expects($this->atLeast($expectedCalls))->method('run');

        $ticker = new Ticker();
        $ticker->addTick(new CallableTick(
            interval: $interval,
            everyN: $everyN,
            callback: [$mock, 'run']
        )
        );

        $ticker->loop(until: new \DateTime($checkAfter));
    }

    public function loopWithTwoTicksDataProvider(): array
    {
        return [
            [
                '+1 sec',
                [Time::SECOND_10TH, 1, 10],
                [Time::SECOND_100TH, 1, 90],
            ],
            [
                '+1 sec',
                [Time::SECOND_10TH, 2, 5],
                [Time::SECOND_1000TH, 1, 950],
            ],
            [
                '+1 sec',
                [Time::SECOND_1000TH, 2, 450],
                [Time::SECOND_1000TH, 1, 900],
            ],
        ];
    }

    /** @dataProvider loopWithTwoTicksDataProvider */
    public function testLoopTwoTicks(string $checkAfter, array $tickOne, array $tickTwo): void
    {
        [$interval, $everyN, $expectedCalls] = $tickOne;
        [$interval2, $everyN2, $expectedCalls2] = $tickTwo;

        $mock = $this->createMock(Tick::class);
        $mock->expects($this->atLeast($expectedCalls))->method('run');

        $mock2 = $this->createMock(Tick::class);
        $mock2->expects($this->atLeast($expectedCalls2))->method('run');

        $ticker = new Ticker();
        $ticker->addTick(new CallableTick(
            interval: $interval,
            everyN: $everyN,
            callback: [$mock, 'run']
        )
        );
        $ticker->addTick(new CallableTick(
            interval: $interval2,
            everyN: $everyN2,
            callback: [$mock2, 'run']
        )
        );

        $ticker->loop(until: new \DateTime($checkAfter));
    }
}
