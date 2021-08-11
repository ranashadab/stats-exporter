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
  NOTE: TO BE CHANGED
  
To install this package you need to get the Gitlab token from Sam or Reid. You will have to specify in your `composer.json` file that you are pulling from a repository. e.g.

```json
{
  "repositories": [
    {
      "type": "git",
      "url": "https://gitlab.com/booj/php-packages/reposicrud"
    }
  ],
  "require": {
    "booj/reposicrud": "dev-v1-dev"
  }
}
```

##### Note:

Please dont commit your token to version control. If your token is committed and published, we will have to revoke the token and all projects using this package will have to update their codebase.

The most secure way to set an access token is to use composers command line tools and run `composer config -g gitlab-token.gitlab.com XXXXXXXXXXXXXXXXXXXXXXX`. According to [this article](https://www.previousnext.com.au/blog/managing-composer-github-access-personal-access-tokens), it is best to generate your own access token, however you need access to the project. As this grows we will get with devops and figure out the best way to handle this without storing the token in the json.

After you have ran `composer update` or `composer install` then we need to register the service provider. In your `bootstrap/app.php` file, place `$app->register(\Booj\Reposicrud\ServiceProvider::class);` where you need it. All this service provider does is register the `validation` and `request` middleware. So if you decide you dont want to register the service provider you can register the route middleware. The provider is there for your convenience.

You are now ready to start using the ReposiCRUD package.

## How to Use

We have to do a couple of things manually:

- ### Register ServiceProvider

    Add this line to providers array in `config\app.php`.

    ```php
    \Booj\StatsExporter\ServiceProvider::class,

    ```

- ### Configure stats_exporter

    Run `php artisan vendor:publish --provider="Booj\StatsExporter\ServiceProvider"` to create the config file and then     manually populate it with class names. These classes should extend the `Booj\StatsExporter\StatsQueries\StatQueryExporter`    class.

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

    Finally run the `php artisan config:cache` command.



- ### Schedule Tasks

    TO BE Updated

## Tests

If you want to run the tests, simple clone this repo and run a `composer install`.
Once installed you simple need to run `./vendor/bin/phpunit tests/` and it will run the tests for you.
