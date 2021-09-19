# TimedLoop

Run an endless loop until it returns something different, but limit it by time

## Installation 

`composer require rikta/timed-loop`

## Usage

### Minimum

Use Case: We want to check that an endpoint is reachable

Until we get any http-response, `@file_get_contents(...)` simply returns `false` *(maybe a bit hacky? anyway, example!)*
which is also the default "continue"-value to keep the loop going.

As soon as the endpoint is up, `@file_get_contents(...)` will return the textual representation.

We expect the result in a few milliseconds or seconds, therefore the default timeout of 10 seconds is sufficient.

```php
use Rikta\TimedLoop\TimedLoop;
$loop = new TimedLoop(fn () => @file_get_contents('http://localhost:8080/health.php'));
$healthResult = $loop();
```

### Verbose

A more verbose example:

```php
use Rikta\TimedLoop\TimedLoop;

class RemoteRepositoryAdapter {
    public function getNextItem(Repository $repository, Options $options): ?object { /*...*/ }
    public function hasNextItem(Repository $repository, Options $options): bool { /*...*/ }
}
$options = new Options(/*...*/);
$repository = new Repository(/*...*/);
$repositoryAdapter = new RemoteRepositoryAdapter;

// instead of an anonymous function you can also provide callables via array
// the first element is the object/class-string/'self', the second one the method
$loop = new TimedLoop([$repositoryAdapter, 'getNextItem']);

// by default it waits until something else than `false`
$loop->untilItReturnsSomethingElseThan(null);

// by default it retries it after 50000 microseconds (1/1_000_000 second)
$loop->retryingAfterMicroseconds(100_000);

// by default it throws an exception after 10 seconds
$loop->forMaximumSeconds(60);

// run the loop until the callable returns a non-null-value, and return said value
$nextItem = $loop($repository, $options);
```

all methods are chainable, therefore the following call would be equivalent:

```php
$nextItem = (new TimedLoop([$repositoryAdapter, 'getNextItem']))
            ->untilItReturnsSomethingElseThan(null)
            ->retryingAfterMicroseconds(100_000)
            ->forMaximumSeconds(60)
            ($repository, $options);
```

### Static

If you're happy with the defaults, you can also just use the static `loop($callable, ...$args)`

```php
// loops until hasNextItem() returns true
TimedLoop::loop([$repositoryAdapter, 'hasNextItem'], $repository, $options);
```