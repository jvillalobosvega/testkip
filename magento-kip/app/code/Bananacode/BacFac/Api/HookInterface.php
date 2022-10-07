<?php

namespace Bananacode\BacFac\Api;

/**
 * Interface Hook
 * @package Bananacode\BacFac\Api
 */
interface HookInterface
{
    /**
     * Send email on payment status update
     *
     * @param string $id
     * @return mixed
     */
    public function notify($id);
}
