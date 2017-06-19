# Conditional retry

Conditionally retry any third party api call.

Sometimes network connection is not working properly and we would like to retry api call. Using this tiny library you can easily retry any action based on specific error condition throwed by exception or returned value.

[![Build Status](https://travis-ci.org/HonzaMac/conditional-retry.svg?branch=master)](https://travis-ci.org/HonzaMac/conditional-retry)


## Usage

Reason for retry is returned via `return $result`. First argument is required api call, second argument is conditional callback, third argument defines how many total runs will api call do.
```php
<?php
require __DIR__ . '/vendor/autoload.php';

retryConditional(function () {
	$result = $this->sms->send('+420800100200', 'Help, I\'m drowning!');
    return $result;
}, function ($returnValue) {
    return !$returnValue;
}, 3);
```

Reason for retry is returned via `RuntiomeException`.
```php
<?php
require __DIR__ . '/vendor/autoload.php';

retryConditional(
    function () { 
    	// do stuff
    	throw new \RuntimeException();
    },
    function ($value, $exception) { return $exception instanceof \RuntimeException;},
    3
);
```

When all retries failed, last return value/exception is returned / re-throwed.
For more information take a look on tests.

## Requirements
- PHP 7.0 is required but using the latest version of PHP is highly recommended

## Installation
You can add this library as a local, per-project dependency to your project using [Composer](https://getcomposer.org/):

    composer require honzamac/conditional-retry

## Contributing

Any contributions are welcome.