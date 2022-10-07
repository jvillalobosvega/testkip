<?php

namespace Bananacode\Kip\Model;

use Bananacode\Kip\Api\MapInterface;

/**
 * Class Map
 * @package Bananacode\Kip\Model
 */
class Map implements MapInterface
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Bananacode\Kip\Helper\Map
     */
    private $_mapHelper;

    /**
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Bananacode\Kip\Helper\Map $mapHelper
     */
    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bananacode\Kip\Helper\Map $mapHelper
    ) {
        $this->_curl = $curl;
        $this->_scopeConfig = $scopeConfig;
        $this->_mapHelper = $mapHelper;
    }

    /**
     * Search near locations
     *
     * @param string $query
     * @return mixed
     */
    public function search($query)
    {
        $centers = json_decode($this->getCenter(), true);
        $key = $this->getKey();
        $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json?location=' . $centers['lat'] . ',' . $centers['lng'] . '&radius=52000&sensor=true&query=' . urlencode($query . ',' . $this->getCountry()) . '&key=' . $key . '&t=m';

        $this->_curl->get($url);
        $response = json_decode($this->_curl->getBody());

        if (isset($response->results[0]->geometry->location)) {
            return (Array)($response->results[0]->geometry->location);
        }

        return [];
    }

    /**
     * Get configured El Salvador cities & departments
     *
     * @return mixed
     */
    public function locations()
    {
        $this->jsonResponse([
            "cities" => $this->_mapHelper->getCities(),
            "departments" => $this->_mapHelper->getDepartments()
        ]);
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->_scopeConfig->getValue('bananacode/google/map_key');
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->_scopeConfig->getValue('bananacode/google/map_country');
    }

    /**
     * @return mixed
     */
    public function getCenter()
    {
        return $this->_scopeConfig->getValue('bananacode/google/map_center');
    }

    /**
     * @param $response
     */
    private function jsonResponse($response)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

}
