<?php

namespace Bananacode\Kip\Api;

/**
 *
 */
interface ReportsInterface
{
    /**
     * Customer recurring report
     *
     * @param integer $id
     * @param string $sort
     * @param string $from
     * @param string $to
     * @param string $email
     * @param string $name
     * @return mixed
     */
    public function recurring($id, $sort, $from = '', $to = '', $email = '', $name = '');

    /**
     * Customer wishlist report
     *
     * @param integer $id
     * @param string $sort
     * @param string $from
     * @param string $to
     * @param string $email
     * @param string $name
     * @return mixed
     */
    public function wishlist($id, $sort, $from = '', $to = '', $email = '', $name = '');

    /**
     * LS Power Bi Orders Data
     *
     * @param string $from
     * @param string $to
     * @param string $status
     * @param integer $count
     * @param integer $page
     *
     * @return mixed
     */
    public function lspowerbiorders($from, $to, $status, $count, $page);
}
