<?php

namespace Schoenef\GraphHopperConnectorBundle\Service;


use GuzzleHttp\Client;
use Schoenef\PhotonOsmConnectorBundle\DependencyInjection\Configuration;

class GraphHopperConnector {

    private $config;

    private $client;

    private $lang;

    public function __construct(array $connectorConfig){
        $this->config = $connectorConfig;

        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->config[Configuration::KEY_API_HOST],
            // You can set any number of default request options.
            'timeout'  => $this->config[Configuration::KEY_TIMEOUT],
        ]);

        $this->lang = $this->config[Configuration::KEY_LANG];
    }


    /**
     * @param $name
     * @param array $filter the filter allows to reduce the results to certain types
     * @return array
     */
    public function searchLocation ($name, $filter = []) {
        $options = [];

        if ($this->lang) {
            $options['lang'] = $this->lang;
        }

        $options['q'] = $name;

        $response = $this->client->request('GET', '',['query' => $options]);

        if ($response->getStatusCode() == '200') {
            return $this->filterResult(json_decode($response->getBody()->getContents(), true)['features'], $filter);
        }

        return [];
    }


    public function filterResult ($featuresArray, $filter = []) {


        foreach ($filter as $key => $allowedValues) {
            $filteredResult = [];
            foreach ($featuresArray as $entry) {
                if (array_key_exists($key, $entry['properties']) && in_array($entry['properties'][$key], $allowedValues)) {
                    $filteredResult[] = $entry;
                }
            }

            $featuresArray = $filteredResult;
        }

        return $featuresArray;
    }

}