<?php

declare(strict_types=1);

namespace Rikta\TimedLoop\Tests;

use Generator;
use PHPUnit\Framework\TestCase;
use Rikta\TimedLoop\LoopTimeoutException;
use Rikta\TimedLoop\TimedLoop;

/**
 * @internal
 *
 * @small
 */
final class TimedLoopTest extends TestCase
{
    /** @test */
    public function doesNotThrowExceptionIfInstructedSo(): void
    {
        $result = (new TimedLoop(static fn () => false))->forMaximumSeconds(0.01)->withoutThrowingException()->invoke();
        self::assertFalse($result);
    }

    /** @test */
    public function invokePassesArguments(): void
    {
        $called = false;
        $first = 1;
        $second = 'two';
        $third = '###';
        $callback = static function ($a, $b, $c) use ($first, $second, $third, &$called) {
            return $called = ($a === $first && $b === $second && $c === $third);
        };
        (new TimedLoop($callback))->invoke($first, $second, $third);
        self::assertTrue($called);
    }

    /** @test */
    public function invokeReturnsResult(): void
    {
        $hello = 'Hello Assertion!';
        $callback = static fn () => $hello;
        $result = (new TimedLoop($callback))->invoke();
        self::assertSame($hello, $result);
    }

    /** @test */
    public function invokingPassesArguments(): void
    {
        $called = false;
        $first = 1;
        $second = 'two';
        $third = '###';
        $callback = static function ($a, $b, $c) use ($first, $second, $third, &$called) {
            return $called = ($a === $first && $b === $second && $c === $third);
        };
        (new TimedLoop($callback))($first, $second, $third);
        self::assertTrue($called);
    }

    /** @test */
    public function invokingReturnsResult(): void
    {
        $hello = 'Hello Assertion!';
        $callback = static fn () => $hello;
        $result = (new TimedLoop($callback))();
        self::assertSame($hello, $result);
    }

    /** @test */
    public function loopPassesArguments(): void
    {
        $called = false;
        $first = 1;
        $second = 'two';
        $third = '###';
        $callback = static function ($a, $b, $c) use ($first, $second, $third, &$called) {
            return $called = ($a === $first && $b === $second && $c === $third);
        };
        TimedLoop::loop($callback, $first, $second, $third);
        self::assertTrue($called);
    }

    /** @test */
    public function loopReturnsResult(): void
    {
        $hello = 'Hello Assertion!';
        $callback = static fn () => $hello;
        $result = TimedLoop::loop($callback);
        self::assertSame($hello, $result);
    }

    /**
     * @testdox forMaximumSeconds($seconds) sets the timeout with an accuracy of $accuracy seconds
     * @dataProvider maxSecondsDataProvider
     *
     * @test
     */
    public function maximumSeconds(float $seconds, float $accuracy): void
    {
        $time = microtime(true);

        try {
            (new TimedLoop(static fn () => false))->forMaximumSeconds($seconds)->invoke();
        } catch (LoopTimeoutException $exception) {
        }
        self::assertEqualsWithDelta($seconds, microtime(true) - $time, $accuracy);
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

    /**
     * @testdox retryingAfterMicroseconds($retry) holds accurate withing $accuracy microseconds over $maximumSeconds seconds
     * @dataProvider retryAfterMicroSecondsDataProvider
     *
     * @test
     */
    public function retryingAfterMicroseconds(int $retry, float $maximumSeconds, float $accuracy): void
    {
        $times = [];
        $callable = static function () use (&$times): bool {
            $times[] = microtime(true);

            return false;
        };

        try {
            (new TimedLoop($callable))->forMaximumSeconds($maximumSeconds)->retryingAfterMicroseconds($retry)->invoke();
        } catch (LoopTimeoutException $exception) {
        }

        $last = 0;
        $differences = [];
        foreach ($times as $time) {
            $differences[] = ($time - $last) * 1_000_000 - 1_000/* a little overhead of the loop etc*/;
            $last = $time;
        }
        array_shift($differences);
        $avg = array_sum($differences) / \count($differences);
        self::assertEqualsWithDelta($retry, $avg, $accuracy);
    }

    /** @test */
    public function throwsException(): void
    {
        $this->expectException(LoopTimeoutException::class);
        (new TimedLoop(static fn () => false))->forMaximumSeconds(0.01)->invoke();
    }

    /** @test */
    public function untilItReturnsSomethingElseThan(): void
    {
        $i = 0;
        $result = 'Hello Assertion!';
        $callable = static function () use (&$i, $result) {
            if (10 < ++$i) {
                return $result;
            }

            return false;
        };
        self::assertSame($result, TimedLoop::loop($callable));
    }
}
