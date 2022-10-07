<?php

namespace Bananacode\Ls\Helper;

use Ls\Omni\Client\Ecommerce\Entity\Order;

/**
 * Class ItemHelper
 * @package Bananacode\Ls\Helper
 */
class ItemHelper extends \Ls\Omni\Helper\ItemHelper
{
    /**
     * This function is overriding in hospitality module
     *
     * Compare one_list lines with quote_item items and set correct prices
     *
     * @param $quote
     * @param Order $basketData
     */
    public function setDiscountedPricesForItems($quote, $basketData)
    {
        return;
    }
}
