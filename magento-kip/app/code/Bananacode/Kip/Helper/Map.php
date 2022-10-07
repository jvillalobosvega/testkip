<?php

namespace Bananacode\Kip\Helper;

/**
 * Class Map
 * @package Bananacode\Kip\Helper
 */
class Map extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->scopeConfig->getValue('bananacode/google/map_key');
    }

    /**
     * @return mixed
     */
    public function getPolygon()
    {
        return $this->scopeConfig->getValue('bananacode/google/map_polygon');
    }

    /**
     * @return mixed
     */
    public function getPolygonRestricted()
    {
        return $this->scopeConfig->getValue('bananacode/google/map_polygon_restricted');
    }

    /**
     * @return mixed
     */
    public function getCenter()
    {
        return $this->scopeConfig->getValue('bananacode/google/map_center');
    }

    /**
     * @return mixed
     */
    public function getDepartments()
    {
        return explode(',', $this->scopeConfig->getValue('bananacode/checkout/departments'));
    }

    /**
     * @return mixed
     */
    public function getCities()
    {
        return explode(',', $this->scopeConfig->getValue('bananacode/checkout/cities'));
    }
}
