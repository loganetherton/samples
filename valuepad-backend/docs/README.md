## Get Started

The quickest way to setup the development environment is to install [vagrant](https://www.vagrantup.com/docs/installation/) with [Laravel Homestead](https://laravel.com/docs/5.4/homestead)

Once everything is installed proceed with the following steps:

* Update`~/.homestead/Homestead.yaml` so that it looks like this:

```code
ip: "192.168.10.10"
memory: 2024
cpus: 1
provider: virtualbox

authorize: ~/.ssh/id_rsa.pub

keys:
    - ~/.ssh/id_rsa

folders:
    - map: /path/to/your/code
      to: /var/www
      
      # this doesn't work on Windows
      type: "nfs" 

sites:
    - map: vp.dev
      to: /var/www/public

databases:
    - valuepad
 
variables:
    - key: APP_ENV
      value: tests
```

* Run the following `vagrant up && vagrant ssh` to run and access the virtual machine
* Run the following `cd /var/www && git clone git@github.com:ascope/valuepad-back-end.git ./` to clone the repository
* Make sure you have `gd` and `mbstring` php extensions installed
* Run the following `cp .env.tests .env` to create a default `env` file
* Run the following `composer install` to pull dependencies
* Run the following `php artisan project:test --env=tests` to run the tests and seed the database with data
* Go to [http://192.168.10.10/v2.0/asc](http://192.168.10.10/v2.0/asc) to see the data from the `asc_gov` table to test the service

## Troubleshooting

##### Problem

```
[Composer\Downloader\TransportException]                                                                         
  The "https://api.github.com/repos/ascope/shared-back-end" file could not be downloaded (HTTP/1.1 404 Not Found)
```

##### Solution

```
$ export GITHUB_ACCESS_TOKEN=github-token-with-full-access
$ composer config -g github-oauth.github.com $GITHUB_ACCESS_TOKEN
$ composer install
```

##### Problem

PHP Fatal error:  Uncaught ReflectionException: Class log does not exist in ... Script php artisan clear-compiled handling the post-update-cmd event returned with error code 255

##### Solution

The .env and .env.tests files need to be updated. This is usually caused by a syntax error, but can be caused by missing values.

Run `composer update` and then `composer install` after fixing errors.

## Overview

The overall system is built around the [./app/Core](https://github.com/ascope/valuepad-back-end/tree/development/app/Core) tier. The `Core` tier dictates the business logic by exposing entities, services and interfaces to the rest of the application. 

Here's the list of tiers composing the application:

* [Core](https://github.com/ascope/valuepad-back-end/tree/development/app/Core) - the whole application is build around this tier
* [API](https://github.com/ascope/valuepad-back-end/tree/development/app/Api) - this tier accepts and handles RESTful API requests. Normally, it accepts and processes incoming requests; delegates handling to the `Core` layer by invoking needed services; prepares responses from the result of the invoked services;
* [Console](https://github.com/ascope/valuepad-back-end/tree/development/app/Console) - mainly, this tier provides console commands to be executed either manually or by a cron job. 
* [DAL](https://github.com/ascope/valuepad-back-end/tree/development/app/DAL) - this tier is responsible for mapping database to entities as well as provides implementations to interfaces used in the `Core` tier.
* [Letter](https://github.com/ascope/valuepad-back-end/tree/development/app/Letter) - this tier is responsible for preparing and sending e-mails
* [Push](https://github.com/ascope/valuepad-back-end/tree/development/app/Push) - this tier is responsible for preparing and sending notifications to customers/subs
* [Mobile](https://github.com/ascope/valuepad-back-end/tree/development/app/Mobile) - this tier is responsible for preparing and sending push notifications
* [Live](https://github.com/ascope/valuepad-back-end/tree/development/app/Live) - this tier is responsible for preparing and sending notifications to the pusher service
* [IoC](https://github.com/ascope/valuepad-back-end/tree/development/app/IoC) - this tier is responsible for wiring together interfaces and implementations respectively.

The `Core` tier is broken down into number of so-called packages representing different parts of the business logic. It is required to list such packages in the [configuration](https://github.com/ascope/valuepad-back-end/blob/master/config/app.php) , so that the system could use this information to automate some routine processes.

**Recommended:** 
 
## Workflow

#### Example #1

* Appraisal Scope sends a request to ValuePad to create a new order.
* ValuePad finds a route defined here [./app/Api/Customer/V2_0/Routes/Orders.php](https://github.com/ascope/valuepad-back-end/blob/development/app/Api/Customer/V2_0/Routes/Orders.php) to handle the request further.
* ValuePad finds [./app/Api/Customer/V2_0/Controllers/OrdersController.php](https://github.com/ascope/valuepad-back-end/blob/development/app/Api/Customer/V2_0/Controllers/OrdersController.php) to handle the request
* However, before doing anything further, ValuePad checks whether the requested controller and the action are allowed to be executed. ValuePad does it with help of [./Libraries/Permissions](https://github.com/ascope/shared-back-end/tree/development/Libraries/Permissions) which takes care of everything regarding permissions by relying on [./app/Api/Customer/V2_0/Controllers/Permissions/OrdersPermissions.php](https://github.com/ascope/valuepad-back-end/blob/development/app/Api/Customer/V2_0/Controllers/Permissions/OrdersPermissions.php) where mapping between actions and permissions is defined.
* Furthermore, ValuePad let [./app/Api/Customer/V2_0/Processors/OrdersProcessor.php](https://github.com/ascope/valuepad-back-end/blob/development/app/Api/Customer/V2_0/Processors/OrdersProcessor.php) validate body of the request to make sure that it follows the schema defined in the processor. If it does not pass the validation, ValuePad throws [./Libraries/Validation/ErrorsThrowableCollection.php](https://github.com/ascope/shared-back-end/blob/development/Libraries/Validation/ErrorsThrowableCollection.php) with all the errors so that [./Libraries/Kangaroo/ExceptionHandler.php](https://github.com/ascope/shared-back-end/blob/development/Libraries/Kangaroo/ExceptionHandler.php) could prepare an according response to send back to the client. It's worth to note that validation gets executed automatically at the time the processor gets injected to the method of the controller handling the request.
* ValuePad proceeds with the `store` method in the controller. The method asks the processor to instantiate and populate [./app/Core/Appraisal/Persistables/CreateOrderPersistable.php](https://github.com/ascope/valuepad-back-end/blob/development/app/Core/Appraisal/Persistables/CreateOrderPersistable.php) with the data passed in the request. The processor does it with help of [./Libraries/Converter/Populator/Populator.php](https://github.com/ascope/shared-back-end/blob/development/Libraries/Converter/Populator/Populator.php).
* The created object is passed to the [./app/Core/Appraisal/Services/OrderService.php](https://github.com/ascope/valuepad-back-end/blob/development/app/Core/Appraisal/Services/OrderService.php) to be handled further by the `create` method.
* The method validates the object within [./app/Core/Appraisal/Validation/CreateOrderValidator.php](https://github.com/ascope/valuepad-back-end/blob/master/app/Core/Appraisal/Validation/CreateOrderValidator.php). If the validation fails, the validator throws [./Libraries/Validation/ErrorsThrowableCollection.php](https://github.com/ascope/shared-back-end/blob/development/Libraries/Validation/ErrorsThrowableCollection.php) with all the errors and proceeds as mentioned above.
* The method creates and saves [./app/Core/Appraisal/Entities/Order.php](https://github.com/ascope/valuepad-back-end/blob/master/app/Core/Appraisal/Entities/Order.php) and notifies the rest of the application about this by broadcasting [./app/Core/Appraisal/Notifications/CreateOrderNotification.php](https://github.com/ascope/valuepad-back-end/blob/master/app/Core/Appraisal/Notifications/CreateOrderNotification.php) with all the required information.
* The system notifies about the created order all interested listeners found in here [https://github.com/ascope/valuepad-back-end/blob/master/config/alert.php](https://github.com/ascope/valuepad-back-end/blob/master/config/alert.php)
* The method returns the created order to the controller where the object gets transformed to an array and consequently transformed to a JSON string which then gets passed to the client.
 
## Test

All the tests can be run with the following command:

`php artisan project:test --env=tests`

You can also narrow tests to a specific path using filtering:

`php artisan project:test --env=tests --filter="V2_0/Customer"` 

The above command will execute all tests in the `Customer` directory.

The initial data seeding is happening here [./seeding/TestsSeeder.php](https://github.com/ascope/valuepad-back-end/blob/master/seeding/TestsSeeder.php).

## Migrations

You will need to run the following command to create a new migration file:

`php doctrine migrations:generate`

The command will create a file in the [./database/migrations](https://github.com/ascope/valuepad-back-end/tree/master/database/migrations) directory.

To run migrations, you will need to execute the following command: 

`php doctrine migrations:migrate`

Normally, you don't have to run the above command because the tests will re-generate the database from existing entities every time you run tests.
