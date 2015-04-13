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

You can perform any kind of trends query by using this function:
```php
    /*
     * Perform a google trends request
     *
     * @param array $parameters
     * @return \Jonasva\GoogleTrends\GoogleTrendsResponse
     */
    public function performRequest(array $parameters)
```

The `$parameters` parameter contains the query conditions.

Example:
```php
    // add search terms (optional for topQueries cid)
    $parameters['terms'] = ['term1', 'term2', 'term3'];

    // set a date range (optional)
    $parameters['dateRange']['start'] = (new \DateTime('2015-01-01'))->format('Y-m-d');
    $parameters['dateRange']['end'] = (new \DateTime())->format('Y-m-d');

    // set a location (optional)
    $parameters['location'] = 'BE';

    // set a category id (optional)
    $parameters['category'] = '0-3';

    // set a cid, there are 3 options:
    $parameters['cid'] = 'graph'; // to return time graph data points and labels
    $parameters['cid'] = 'topQueries'; // to return the top queries
    $parameters['cid'] = 'risingQueries'; // to return rising queries

    $response = LaravelGoogleTrends::performRequest($parameters);
```

You can then format the response to a more usable data format:
```php
    // to get an array of GoogleTrendsTerm objects
    $response->getTermsObjects();

    // to get formatted data suitable for creating a line chart
    // can only be used with $parameters['cid'] = 'graph'
    $response->getFormattedData();
```

## Methods

Get labels and data points for a graph of 1 or more terms for a given period
```php
    /*
     * Get labels and data points for a graph of 1 or more terms for a given period
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param array $terms
     * @param string $location (optional)
     * @param bool $fillEmpties (optional - if true, an array of zeros will be added for terms with no results)
     * @return array
     */
    public function getTermsGraphForPeriod(\DateTime $startDate, \DateTime $endDate, array $terms, $location = null, $fillEmpties = true)
```
