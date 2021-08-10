# ReposiCRUD
A simple Lumen (Lumen and Lumen only) package to handle simple CRUD operations.


## Motivation
Clean and consistent CRUD operations, removes the copy pasta coding when implementing new models. Responses will be consistent across all applications that implement the package. We get to go from 1 hour or longer to implement CRUD to as little as 10 - 15 minutes. Also any and all endpoints that are implemented through this package are very easy to override. You also can opt in or out of anything within this package, and should not have any negative side effects.

## Table of Contents
- [Code Style](#code-style)
- [Technologies](#technologies)
- [Features](#features)
- [Installation](#installation)
- [How to Use](#how-to-use)
    - [Repositories](#repositories)
    - [Controllers](#controllers)
    - [Resources](#resources)
    - [Validators](#validators)
    - [Middleware](#middleware)
- [Tests](#tests)
- [Contribute](#contribute)

## Code Style
We follow PSR2 standards.

## Technologies
- PHP 7.3
- Lumen 5.7
- PHPUnit 7 (dev)
- Mockery (dev)

## Features
- Repository pattern for controllers (doubles as in PHP memory model cache).
- Middleware to validate all data incoming before the data hits controllers.
- Full CRUD operations for any controller (Create, Read, Index, Update, Delete).
- Easy validation classes that allow you to specify validation on a per controller action basis.
- Resource classes that allow complete control over data output to the end user.

## Installation

To install this package you need to get the Gitlab token from Sam or Reid. You will have to specify in your `composer.json` file that you are pulling from a repository. e.g.
```json
{
  "repositories": [
    {
      "type": "git",
      "url": "https://gitlab.com/booj/php-packages/reposicrud"
    },
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

### Quick Start
Run ```php artisan make:reposicrud:all``` to generate a full skeleton for your CRUD. This will create the following:
* Eloquent Model
* Eloquent Repository
* Model Resource and Resource Collection
* CRUD Controller
* Controller Validator
* Controller Policy 

NOTE: You will need to write your own routes and middleware (see [Middleware], and policy checks (see [Policies]. See the instructions below. 

### Repositories

The controllers, and the repositories are the central units of this application. In order to implement the CRUDController, your model needs to have a repository that inherits from the `Booj\Reposicrud\Repositories\Eloquent\Repository` class. All you have to do is define a constructor for the Repository and that class is up and running.

For an example, look at the [Example Repository](tests/data/TestRepository.php).

### Controllers

The next step is to create a controller and extend the `CRUDController`. If you want to utilize resources, explained [here](#resources), then you simply need to implement the [ResourceInterface](src/Controllers/Contracts/ResourceInterface.php). The most important part of the controller is to set your Repository in the constructor of the controller. This allows Lumen's IOC container to inject the repository into your controller. And since in the repository, we type hinted the model, the container also knows to search itself for an instance of the model and inject that properly as well. You now have full CRUD operations. At this point in time though, you do not have validation for your controller actions. If you want to have the validations middleware work and pre-validate your data before the user info even hits the controller, you need to also implement the [Validator Interface](src/Controllers/Contracts/ValidatorInterface.php). 

If you need to add any more endpoints in your controller, you can easily access the model through the controllers repository, or just simply make a call to your database through the Eloquent ORM like you would other wise and it is business as normal.

For an example, have a look at the [Example Controller](tests/data/TestController.php).

### Resources

For a very detailed explanation of resources please see [Laravel's documentation on Resources](https://laravel.com/docs/5.7/eloquent-resources).

Long story short, resources allow you a very nice way to control the output of your data to an end user. The [Request Middleware](src/Middleware/RequestMiddleware.php) looks for the implementation of the [Resource Interface](src/Controllers/Contracts/ResourceInterface.php), and if your endpoint returns either an Eloquent Model or a Collection, then it knows to apply the resource so your data is always output as you specified it. This allows for consistent responses across the application, while in the application code, you can access your models properties exactly as you expect.

For an example, take a look at the [Example Resource](tests/data/TestResource.php);

### Validators

All validator classes must extend the [PolicyValidator](/Validators/PolicyValidator.php) class. This class does a lot of heavy lifting for you before your users data even hits the controller. All input is validated before your data hits the controller, so if validation fails, it errors out before coming near the database.
 
 One method of interest is the arguments method, this will take in whatever controller action, and you can determine how to find the model necessary. Look at the [arguements method](https://gitlab.com/booj/php-packages/reposicrud/blob/56a50a43cbd66b1436f89e6b1fee084fb31bc52d/src/validators/PolicyValidator.php#L53) for an example. The parent method handles all basic CRUD operations. This method is important, it is through this method that the [validation middleware](src/Middleware/ValidationMiddleware.php) finds and binds the model to the IOC container, so you only have to make one search on the database. This effectively caches your model in your repository once the repository is created, very convenient.

When it comes to validation, you have a per controller action control. All you have to do is add a protected property that follows our naming convention, to your validator class e.g.
    ```php
    protected $index_rules = [//your validation rules]
    ``` 
You simply name your action followed  by `_rules`, and the validation middleware will run its validation for that particular action.

As stated in the [controllers section](#Controllers), in order for the validation middleware to pick up your validator you must implement the [ValidationInterface](src/Controllers/Contracts/ValidatorInterface.php).

For an example, take a look at the [Example Validator](tests/data/TestValidator.php) and the [Example Controller](tests/data/TestController.php).

### Middleware

This one is easy, can pick and choose which middleware to utilize on a per route basis. Since we registered the middleware in the [service provider](src/ServiceProvider.php), there is nothing more for you to do, other than tell Lumen in the routes that you want to utilize the middleware. Unless of course you decided to register the middleware yourself and bypass the service provider.

e.g.

```php
<?php

$router->group(['prefix' => 'tester/', 'middleware' => ['request', 'validation']],
    function () use ($router) {
        $router->post('/', ['uses' => 'TestController@create']);
        $router->get('/{id}', ['uses' => 'TestController@read']);
        $router->put('/{id}', ['uses' => 'TestController@update']);
        $router->delete('/{id}', ['uses' => 'TestController@delete']);
    });
```

### Policies
All Policy classes must have methods with the same name as seen in the Controller class. Each Policy method accept the currently logged in user as a parameter, and will return true or false if the user is allowed to access the Controller method of the same name. Methods that do not appear in the Policy class are denied access by default.

```php
<?php
// ExampleController has a function with the signature: index(Request $request)
// The policy is enforced like this:
class ExamplePolicy 
{
    pubic function index(\App\User $user)
    {
         // Do a check here and return true if the controller function is allowed to be called.
         // In this case, only users named "admin" are allowed to view the index.
         return $user->name == 'admin';
    }
}
```
## Tests

If you want to run the tests, simple clone this repo and run a `composer install`.
Once installed you simple need to run `./vendor/bin/phpunit` and it will run the tests for you.

## Contribute

Please feel free to clone, make a new branch, write your feature, and tests. Once all tests are passing please send a merge request and we can happily do a code review. As time continues I will be adding more tests to this repo just to make sure that it is bullet proof. Do make a note, if there are not tests associated with your merge request, your merge request will be denied until there are tests covering what you are adding.
