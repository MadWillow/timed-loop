<?php

declare(strict_types=1);

namespace Rikta\TimedLoop;

use RuntimeException;

/**
 * Exception is thrown when a TimedLoop reaches it's timeout without returning anything else than the "continue"-value.
 */
final class LoopTimeoutException extends RuntimeException
{
    public function __construct(float $secondsPassed, float $secondsLimit, $continueValue)
    {
        parent::__construct(sprintf(
            'Loop timed out after %01.3f seconds (limit: %01.3f seconds) not returning %s',
            $secondsPassed,
            $secondsLimit,
            var_export($continueValue, true),
        ));
    }
}
