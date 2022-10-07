<?php

namespace Bananacode\Kipping\Api;

/**
 * Created by PhpStorm.
 * User: pablogutierrez
 * Date: 2020-02-09
 * Time: 23:02
 */
interface KippingInterface
{
    /**
     * Get available delivery schedules
     *
     * @return mixed
     */
    public function schedules();

    /**
     * Set custom kipping data on session
     *
     * @param string $data
     * @return mixed
     */
    public function session($data);

    /**
     * Set custom kipping data on order
     *
     * @param string $data
     * @return mixed
     */
    public function comment($data);
}
