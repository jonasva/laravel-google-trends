<?php namespace Jonasva\LaravelGoogleTrends;

use Illuminate\Config\Repository;

use Jonasva\GoogleTrends\GoogleSession;
use Jonasva\GoogleTrends\GoogleTrendsRequest;

use Cache;


class LaravelGoogleTrends
{
    /**
     * LaravelGoogleTrends session.
     *
     * @var string
     */
    private $session;

    /**
     * Illuminate config repository instance.
     *
     * @var \Illuminate\Config\Repository
     */
    private $config;

    /**
     * Create a new LaravelGoogleTrends instance.
     *
     * @param  \Illuminate\Config\Repository  $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;

        $sessionConfig = [
            'email'     =>  $this->config->get('laravel-google-trends::username'),
            'password'  =>  $this->config->get('laravel-google-trends::password'),
            'recovery-email'    =>  $this->config->get('laravel-google-trends::recovery-email'),
        ];

        $sessionCacheName = $this->determineCacheName(['sessionCache', $this->config->get('laravel-google-trends::username')]);

        if ($this->useSessionCache() && Cache::has($sessionCacheName)) {
            $cookieJar = Cache::get($sessionCacheName);
            $this->session = new GoogleSession($sessionConfig);
            $this->session->setCookieJar($cookieJar);

            // double check if we are authenticated
            if ($this->session->checkAuth() == false) {
                $this->session->authenticate();
            }
        }
        else {
            $this->session = (new GoogleSession($sessionConfig))->authenticate();
            $cookieJar = $this->session->getCookieJar();

            if ($this->useSessionCache()) {
                Cache::put($sessionCacheName, $cookieJar, $this->config->get('laravel-google-trends::session-cache-lifetime'));
            }
        }
    }

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
    {
        $parameters['terms'] = $terms;

        $parameters['dateRange'] = [
            'start' => $startDate->format('Y-m-d'),
            'end'   => $endDate->format('Y-m-d'),
        ];

        $parameters['cid'] = 'graph';

        !$location ?: $parameters['location'] = $location;

        $content = $this->performRequest($parameters)->getFormattedData();

        if ($fillEmpties) {
            $arrayLength = count($content['Date']);
            // create empties
            foreach($terms as $term) {
                $term = strtolower($term);
                if (!isset($content[$term])) {
                    $content[$term] = array_fill(0, $arrayLength, 0);;
                }
            }
        }

        return $content;
    }

    /*
     * Perform a google trends request
     *
     * @param array $parameters
     * @return \Jonasva\GoogleTrends\GoogleTrendsResponse
     */
    public function performRequest(array $parameters)
    {
        $cacheName = $this->determineCacheName($parameters);

        if ($this->useCache() && Cache::has($cacheName)) {
            $response = Cache::get($cacheName);
        }
        else {
            $request = new GoogleTrendsRequest($this->session);

            // add terms
            if (isset($parameters['terms'])) {
                foreach ($parameters['terms'] as $term) {
                    $request->addTerm($term);
                }
            }

            // date range
            if (isset($parameters['dateRange'])) {
                $request->setDateRange(new \DateTime($parameters['dateRange']['start']), new \DateTime($parameters['dateRange']['end']));
            }

            // location
            if (isset($parameters['location'])) {
                $request->setLocation($parameters['location']);
            }

            // category
            if (isset($parameters['category'])) {
                $request->setLocation($parameters['category']);
            }

            // cid
            if (isset($parameters['cid'])) {
                switch ($parameters['cid']) {
                    case 'graph':
                        $request->getGraph();
                        break;
                    case 'topQueries':
                        $request->getTopQueries();
                        break;
                    case 'risingQueries':
                        $request->getRisingQueries();
                        break;
                    default:
                        $request->getTopQueries();
                        break;
                }
            }

            $response = $request->send();

            if ($this->useCache()) {
                Cache::put($cacheName, $response, $this->config->get('laravel-google-trends::api-call-cache-lifetime'));
            }
        }


        return $response;
    }

    /**
     * Determine the cache name for the set of query properties given
     *
     * @param array $properties
     * @return string
     */
    private function determineCacheName(array $properties)
    {
        return 'jonasva.laravel-google-trends.' . md5(serialize($properties));
    }

    /**
     * Determine whether or not to cache API responses
     *
     * @return bool
     */
    private function useCache()
    {
        return $this->config->get('laravel-google-trends::api-call-cache-lifetime') > 0;
    }

    /**
     * Determine whether or not to cache the session
     *
     * @return bool
     */
    private function useSessionCache()
    {
        return $this->config->get('laravel-google-trends::session-cache-lifetime') > 0;
    }
} 
