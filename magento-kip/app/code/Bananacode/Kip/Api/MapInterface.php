<?php

namespace Bananacode\Kip\Api;

/**
 * Interface MapInterface
 * @package Bananacode\Kip\Api
 */
interface MapInterface
{
    /**
     * Search near locations
     *
     * @param string $query
     * @return mixed
     */
    public function search($query);

    /**
     * Get configured El Salvador cities & departments
     *
     * @return mixed
     */
    public function locations();
}
