<?php

namespace Schoenef\GraphHopperConnectorBundle\Service;


use GuzzleHttp\Client;
use Schoenef\GraphHopperConnectorBundle\DependencyInjection\Configuration;

class GraphHopperConnector {

    private $config;

    private $client;

    private $lang;
    private $country;
    private $provider;
    private $autocomplete;
    private $key;

    public function __construct(array $connectorConfig){
        $this->config = $connectorConfig;

        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->config[Configuration::KEY_API_HOST],
            // You can set any number of default request options.
            'timeout'  => $this->config[Configuration::KEY_TIMEOUT],
        ]);

        $this->lang = $this->config[Configuration::KEY_LANG];
        $this->country = $this->config[Configuration::KEY_COUNTRY];
        $this->provider = $this->config[Configuration::KEY_PROVIDER];
        $this->provider = $this->config[Configuration::KEY_PROVIDER];
        $this->key = $this->config[Configuration::KEY_API_KEY];
    }


    /**
     * this function will convert the graphhopper result to geojson http://geojson.org/
     *
     * @param $name
     * @param array $filter the filter allows to reduce the results to certain types
     * @return array|bool
     */
    public function searchLocation ($name, $filter = []) {
        $options = [];

        $options['provider'] = $this->provider;
        $options['key'] = $this->key;

        if ($this->lang) {
            $options['lang'] = $this->lang;
        }

        if ($this->country) {
            $options['country'] = $this->country;
        }

        // autocomplete is only available for gisgraphy
        if ($this->provider === Configuration::PROVIDER_GISGRAPHY && $this->autocomplete) {
            $options['autocomplete'] = 'true';
        }

        $options['q'] = $name;

        // https://graphhopper.com/api/1/geocode?q=berlin&locale=de&country=DE&autocomplete=true&key=7bd83ec8-fcda-45dc-957e-c1f66376ea1a&provider=gisgraphy

        $response = $this->client->request('GET', '/api/1/geocode',['query' => $options]);

        if ($response->getStatusCode() == '200') {
            return $this->filterResult(json_decode($response->getBody()->getContents(), true)['hits'], $filter);
        }

        return false;
    }


    public function filterResult ($hitsArray, $filter = []) {

        if (count($filter)) {
            foreach ($filter as $key => $allowedValues) {
                $filteredResult = [];
                foreach ($hitsArray as $entry) {

                    $entry = $this->convertToGeoJSON($entry);

                    if (array_key_exists($key, $entry['properties']) && in_array($entry['properties'][$key], $allowedValues)) {
                        $filteredResult[] = $entry;
                    }
                }

                $hitsArray = $filteredResult;
            }
        } else {
            // we still need to convert this into geoJson ;)
            foreach ($hitsArray as $index => $entry) {
                $hitsArray[$index] = $this->convertToGeoJSON($entry);
            }
        }

        return $hitsArray;
    }

    public function convertToGeoJSON ($entry) {
        $geoEntry = [
            "type" => "Feature",
            "geometry" => [
                "type" => "Point",
                "coordinates" => []
            ],
            "properties" => []
        ];

        foreach ($entry as $attr => $val) {
            switch ($attr) {
                default:
                    $geoEntry['properties'][$attr] = $val;
                    break;
                case 'point':
                    $geoEntry['geometry']['coordinates'] = [ $val['lng'], $val['lat'] ];
                    break;
            }
        }

        if ($this->provider === Configuration::PROVIDER_GISGRAPHY) {
            // gisgraphy can do autocomplete, but has a super limited result set

            if (!array_key_exists('name', $entry)) {
                // this is a full auto complete set, let's see what we got

                if (!array_key_exists('city', $entry)) {
                    $geoEntry['properties']['name'] = $entry['street'];
                    $geoEntry['properties']['city'] = $entry['street'];
                    unset($geoEntry['properties']['street']);

                }

            }

        }

        return $geoEntry;
    }

}