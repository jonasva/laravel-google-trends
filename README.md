# Laravel Google Trends unofficial API

Laravel google trends provides an easy way to make queries to Google Trends. It is based on this package: https://github.com/jonasva/google-trends.

## Installation

To get the latest version of LaravelGoogleTrends require it in your `composer.json` file.

~~~
"jonasva/laravel-google-trends": "dev-master"
~~~

Run `composer update jonasva/laravel-google-trends` to install it.

Once LaravelGoogleTrends is installed you need to register its service provider with your application. Open `app/config/app.php` and find the `providers` key.

~~~php
'providers' => array(

    'Jonasva\LaravelGoogleTrends\LaravelGoogleTrendsServiceProvider',

)
~~~

A Facade for easy access is also included. You can register the facade in the `aliases` key of your `app/config/app.php` file.

~~~php
'aliases' => array(

    'LaravelGoogleTrends' => 'Jonasva\LaravelGoogleTrends\Facades\LaravelGoogleTrends',

)
~~~

### Publish the configurations

Run this on the command line from the root of your project:

~~~
$ php artisan config:publish jonasva/laravel-google-trends
~~~

A configuration file will be published to `app/config/packages/jonasva/laravel-google-trends/config.php`

### Config

#### Google account credentials

To make Google trends queries, you need to be logged into a google account. If you are not, you will hit the request quota after just a couple of requests. Your account credentials need to filled out in the config file. You also need to have a recovery email setup, as Google sometimes requires it to verify your log in.

#### Cache

Google trends responses get cached for 1 day by default. You can change this by altering the `api-call-cache-lifetime`.

The Google session you receive after authenticating can be cached as well (which is useful to speed things up). Its cache expiration time can be changed by altering the `session-cache-lifetime`.

## Usage

Coming soon