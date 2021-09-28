<?php

declare(strict_types=1);

namespace Rikta\TimedLoop;

class TimedLoop
{
    /** function that shall be looped */
    private $callable;

    /** value that must be returned by the looped method to keep the loop going */
    private bool $continueReturn = false;

    /** maximum amount of seconds to run until an exception shall be thrown */
    private float $maxSeconds = 10.0;

    /** microseconds between end of a call and a new try */
    private int $retryAfterMicroseconds = 5_0000;

    /** shall an exception be thrown when the timeout is reached? */
    private bool $throwsException = true;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * Invokes the registered loop.
     *
     * executes $this->callable forwarding the arguments 1:1,
     * until it returns something else than $this->continueReturn;
     * throws exception if it exceeds $this->maxSeconds runtime unless instructed otherwise;
     * returns result of the callable
     *
     * @return mixed
     */
    public function __invoke(...$args)
    {
        $start = microtime(true);
        $end = $start + $this->maxSeconds;
        while (true) {
            $result = ($this->callable)(...$args);

            if ($result !== $this->continueReturn) {
                return $result;
            }

            if (($current = microtime(true)) > $end) {
                if ($this->throwsException) {
                    throw new LoopTimeoutException($current - $start, $this->maxSeconds, $this->continueReturn);
                }

                return $result;
            }
            usleep($this->retryAfterMicroseconds);
        }
    }

    /** sets the maximum amount of seconds to run until an exception shall be thrown. */
    public function forMaximumSeconds(float $maxSeconds): self
    {
        $this->maxSeconds = $maxSeconds;

        return $this;
    }

    /**
     * dedicated method for invoking __invoke.
     * depending on the context it may look weird to calling an object without specifying a method.
     */
    public function invoke(...$args)
    {
        return $this(...$args);
    }

    /** sets the amount of microseconds between end of a call and a new try. */
    public function retryingAfterMicroseconds(int $microseconds): self
    {
        $this->retryAfterMicroseconds = $microseconds;

        return $this;
    }

    /**
     * sets the value that must be returned by the looped method to keep the loop going.
     *
     * @param mixed $value
     */
    public function untilItReturnsSomethingElseThan($value): self
    {
        $this->continueReturn = $value;

        return $this;
    }

    /** disables the throwing of an exception on timeout. */
    public function withoutThrowingException(): self
    {
        $this->throwsException = false;

        return $this;
    }

    /** static shortcut for the initialisation & execution of a loop with default parameters. */
    public static function loop(callable $callable, ...$args)
    {
        return (new self($callable))(...$args);
    }
}
