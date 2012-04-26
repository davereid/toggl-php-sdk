# Toggl PHP SDK #

Provides a re-usable PHP library for interacting with the Toggl time tracking
system's API.

* Author: Dave Reid http://www.davereid.net/
* Website: http://davereid.github.com/toggl-php-sdk/
* License: GPLv2/MIT
* Thanks: [tanel](https://github.com/tanel)
* [![Build Status](https://secure.travis-ci.org/davereid/toggl-php-sdk.png?branch=master)](http://travis-ci.org/davereid/toggl-php-sdk)

## Requirements ##

* PHP 5.3 or higher
* [cURL](http://us.php.net/manual/en/book.curl.php) extension
* [JSON](http://us.php.net/manual/en/book.json.php) extension
* [PHPUnit](http://www.phpunit.de/) (for unit testing)

## Usage ##

```
<?php
$api_token = '00000000000000000000000000000000'; // Valid Toggl.com API token
$connection = new TogglConnection($api_token);
$time_entry = TogglTimeEntry::load($connection, 1); // Load time entry #1.
$time_entry->description = 'New description for #1.'
$time_entry->save();
?>
```

## License ##

The Toggl PHP SDK is dual licensed under the MIT and GPLv2 licenses.

## Unit Tests ##

To run the unit tests included with the SDK, you must have PHPUnit installed.
From the Toggl SDK directory, run `phpunit tests` to run all tests.
