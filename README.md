# StatsExporter

A Lumen/Laravel package to export statistics.

## Motivation

We need to export statistics from multiple booj projects. Instead of adding the logic in each project, we'll use this package.

## Table of Contents

- [Code Style](#code-style)
- [Technologies](#technologies)
- [Features](#features)
- [Installation](#installation)
- [How to Use](#how-to-use)
  - [Register Service Provider](#register-serviceProvider)
  - [Configure Stats Exporter](#configure-stats_exporter)
  - [Schedule Tasks](#schedule-tasks)
- [Tests](#tests)

## Code Style

We follow PSR2 standards.

## Technologies

- PHP 7.4
- Lumen 6.0
- PHPUnit 7 (dev)
- Mockery (dev)

## Features

- Open-closed. Open for extension, but closed for modification. Extend StatsQueryExporters and provide your own query.
- Provides migration and model to go with it.
- A custom command to get stats for custom time period.

## Installation

To Be Added.

## How to Use

We have to do a couple of things manually:

- ### Register ServiceProvider

  * Lumen:
 
    Add this line to `bootstrap\app.php`.

    ```php
    /**
     * Booj/StatsExporter
    */
    $app->register(\Booj\StatsExporter\ServiceProvider::class);
    ```

  * Laravel:

    Add this line to providers array in `config\app.php`.

    ```php
    \Booj\StatsExporter\ServiceProvider::class,

    ```

- ### Configure stats_exporter

  * Lumen:

    Create a file named stats_exporter.php in your config directory. This file should contain all the Query Classes.
    Add this line to`bootstrap\app.php`.
    
    ```php
    /**
     * Booj/StatsExporter
    */
    $app->configure('stats_exporter');

    ```

  * Laravel:

    Run `php artisan vendor:publish --provider="Booj\StatsExporter\ServiceProvider"` to create the config file and then   manually populate it with class names. These classes should extend the  `Booj\StatsExporter\StatsQueries\StatQueryExporter` class.

    Finally run the `php artisan config:cache` command.

  Following is a sample for the contents of file.
  ```php
    return [
        "exporter_classes" => [
            \App\Exports\StatsQueries\FailedJobException::class,
            \App\Exports\StatsQueries\PropertySearchesCreated::class,
            \App\Exports\StatsQueries\UsersCreated::class,
            \App\Exports\StatsQueries\PropertySearchSendHistory::class,
            \App\Exports\StatsQueries\TokenBlacklist::class,
            \App\Exports\StatsQueries\PasswordReset::class
        ]
    ];
    ```

- ### Schedule Tasks
  To Be Added.

## Tests

If you want to run the tests, simple clone this repo and run a `composer install`.
Once installed you simply need to run `./vendor/bin/phpunit tests/` and it will run the tests for you.
