# TimedLoop

[![packagist name](https://badgen.net/packagist/name/rikta/timed-loop)](https://packagist.org/packages/rikta/timed-loop)
[![version](https://badgen.net/packagist/v/rikta/timed-loop/latest?label&color=green)](https://github.com/RiktaD/timed-loop/releases)
[![php version](https://badgen.net/packagist/php/rikta/timed-loop)](https://github.com/RiktaD/timed-loop/blob/main/composer.json)

[![license](https://badgen.net/github/license/riktad/timed-loop)](https://github.com/RiktaD/timed-loop/blob/main/LICENSE.md)
[![GitHub commit activity](https://img.shields.io/github/commit-activity/m/riktad/timed-loop)](https://github.com/RiktaD/timed-loop/graphs/commit-activity)
[![open issues](https://badgen.net/github/open-issues/riktad/timed-loop)](https://github.com/RiktaD/timed-loop/issues?q=is%3Aopen+is%3Aissue)
[![closed issues](https://badgen.net/github/closed-issues/riktad/timed-loop)](https://github.com/RiktaD/timed-loop/issues?q=is%3Aissue+is%3Aclosed)

[![ci](https://badgen.net/github/checks/riktad/timed-loop?label=ci)](https://github.com/RiktaD/timed-loop/actions?query=branch%3Amain+workflow%3A%22Testing+Query%22+workflow%3Acreate-release++)
[![dependabot](https://badgen.net/github/dependabot/riktad/timed-loop)](https://dependabot.com)
[![maintainability score](https://badgen.net/codeclimate/maintainability/RiktaD/timed-loop)](https://codeclimate.com/github/RiktaD/timed-loop)
[![tech debt %](https://badgen.net/codeclimate/tech-debt/RiktaD/timed-loop)](https://codeclimate.com/github/RiktaD/timed-loop/issues)
[![maintainability issues](https://badgen.net/codeclimate/issues/RiktaD/timed-loop?label=maintainability%20issues)](https://codeclimate.com/github/RiktaD/timed-loop/issues)


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
// you can also use $loop->invoke($repository, $options) if you don't like invoking variables ;)
```

all methods are chainable, therefore the following call would be equivalent:

```php
$nextItem = (new TimedLoop([$repositoryAdapter, 'getNextItem']))
            ->untilItReturnsSomethingElseThan(null)
            ->retryingAfterMicroseconds(100_000)
            ->forMaximumSeconds(60)
            ->invoke($repository, $options);
```

### Static

If you're happy with the defaults, you can also just use the static `loop($callable, ...$args)`

```php
// loops until hasNextItem() returns true
TimedLoop::loop([$repositoryAdapter, 'hasNextItem'], $repository, $options);
```
