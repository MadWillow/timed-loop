<?php declare(strict_types=1);
namespace Rikta\TimedLoop\Tests;

use Generator;
use PHPUnit\Framework\TestCase;
use Rikta\TimedLoop\LoopTimeoutException;
use Rikta\TimedLoop\TimedLoop;

class TimedLoopTest extends TestCase
{
    public function testThrowsException(): void
    {
        $this->expectException(LoopTimeoutException::class);
        (new TimedLoop(fn () => false))->forMaximumSeconds(0.01)->invoke();
    }

    public function testDoesNotThrowExceptionIfInstructedSo(): void
    {
        $result = (new TimedLoop(fn () => false))->forMaximumSeconds(0.01)->withoutThrowingException()->invoke();
        $this->assertFalse($result);
    }

    /**
     * @testdox forMaximumSeconds($seconds) sets the timeout with an accuracy of $accuracy seconds
     * @dataProvider maxSecondsDataProvider
     */
    public function testMaximumSeconds(float $seconds, float $accuracy): void {
        $time = microtime(true);
        try {
            (new TimedLoop(fn () => false))->forMaximumSeconds($seconds)->invoke();
        } catch (LoopTimeoutException $exception) {}
        self::assertEqualsWithDelta($seconds, microtime(true)-$time, $accuracy);
    }

    public function testUntilItReturnsSomethingElseThan(): void {
        $i = 0;
        $result = 'Hello Assertion!';
        $callable = static function () use (&$i, $result) {
            if (++$i > 10) {
                return $result;
            }
            return false;
        };
        $this->assertEquals($result, TimedLoop::loop($callable));
    }

    /**
     * @testdox retryingAfterMicroseconds($retry) holds accurate withing $accuracy microseconds over $maximumSeconds seconds
     * @dataProvider retryAfterMicroSecondsDataProvider
     */
    public function testRetryingAfterMicroseconds(int $retry, float $maximumSeconds, float $accuracy): void {
        $times = [];
        $callable = static function () use (&$times): bool {
            $times[] = microtime(true);
            return false;
        };
        try {
        (new TimedLoop($callable))->forMaximumSeconds($maximumSeconds)->retryingAfterMicroseconds($retry)->invoke();
        } catch (LoopTimeoutException $exception) {}

        $last = 0;
        $differences = [];
        foreach ($times as $time) {
            $differences[] = ($time - $last) * 1_000_000 - 1_000/* a little overhead of the loop etc*/;
            $last = $time;
        }
        array_shift($differences);
        $avg = array_sum($differences)/\count($differences);
        $this->assertEqualsWithDelta($retry, $avg, $accuracy);
    }

    public function testInvokePassesArguments(): void
    {
        $called = false;
        $first = 1;
        $second = 'two';
        $third = '###';
        $callback = function ($a, $b, $c) use ($first, $second, $third, &$called) {
            return $called = ($a === $first && $b === $second && $c === $third);
        };
        (new TimedLoop($callback))->invoke($first, $second, $third);
        $this->assertTrue($called);
    }

    public function testInvokeReturnsResult(): void
    {
        $hello = 'Hello Assertion!';
        $callback = fn () => $hello;
        $result = (new TimedLoop($callback))->invoke();
        $this->assertEquals($hello, $result);
    }

    public function testInvokingPassesArguments(): void
    {
        $called = false;
        $first = 1;
        $second = 'two';
        $third = '###';
        $callback = function ($a, $b, $c) use ($first, $second, $third, &$called) {
            return $called = ($a === $first && $b === $second && $c === $third);
        };
        (new TimedLoop($callback))($first, $second, $third);
        $this->assertTrue($called);
    }

    public function testInvokingReturnsResult(): void
    {
        $hello = 'Hello Assertion!';
        $callback = fn () => $hello;
        $result = (new TimedLoop($callback))();
        $this->assertEquals($hello, $result);
    }

    public function testLoopPassesArguments(): void
    {
        $called = false;
        $first = 1;
        $second = 'two';
        $third = '###';
        $callback = function ($a, $b, $c) use ($first, $second, $third, &$called) {
            return $called = ($a === $first && $b === $second && $c === $third);
        };
        TimedLoop::loop($callback, $first, $second, $third);
        $this->assertTrue($called);
    }

    public function testLoopReturnsResult(): void
    {
        $hello = 'Hello Assertion!';
        $callback = fn () => $hello;
        $result = TimedLoop::loop($callback);
        $this->assertEquals($hello, $result);
    }

    public function maxSecondsDataProvider(): Generator
    {
        yield [0.1, 0.05];
        yield [0.5, 0.05];
    }

    public function retryAfterMicroSecondsDataProvider(): Generator
    {
        yield [50_000, 0.1, 5_000];
        yield [100_000, 0.5, 5_000];
    }
}