<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 */

namespace Facebook\BusinessExtension\Block\Pixel;

use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order;

class Purchase extends Common
{
    /**
     * @return string
     */
    public function getContentIDs()
    {
        $productIds = [];
        /** @var Order $order */
        $order = $this->fbeHelper->getObject(\Magento\Checkout\Model\Session::class)->getLastRealOrder();
        if ($order) {
            $items = $order->getItemsCollection();
            /**
             * @var $productModel \Magento\Catalog\Api\ProductRepositoryInterface
             */
            //$productModel = $this->objectManager->get(\Magento\Catalog\Model\Product::class);
            $productModel = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
            foreach ($items as $item) {
                try {
                    // @todo do not load product model in loop - this can be a performance killer, use product collection
                    $product = $productModel->getById($item->getProductId());
                    $productIds[] = $product->getId();
                } catch (\Exception $e) {

                }
            }
        }
        return $this->arrayToCommaSeparatedStringValues($productIds);
    }

    public function getValue()
    {
        $order = $this->fbeHelper->getObject(\Magento\Checkout\Model\Session::class)->getLastRealOrder();
        /** @var Order $order */
        if ($order) {
            $subtotal = $order->getSubTotal();
            if ($subtotal) {
                $priceHelper = $this->fbeHelper->getObject(\Magento\Framework\Pricing\Helper\Data::class);
                return $priceHelper->currency($subtotal, false, false);
            }
        } else {
            return null;
        }
    }

    public function getContents()
    {
        $contents = [];
        $order = $this->fbeHelper->getObject(\Magento\Checkout\Model\Session::class)->getLastRealOrder();
        /** @var Order $order */
        if ($order) {
            $priceHelper = $this->objectManager->get(\Magento\Framework\Pricing\Helper\Data::class);
            $items = $order->getItemsCollection();
            /**
             * @var $productModel \Magento\Catalog\Api\ProductRepositoryInterface
             */
            //$productModel = $this->objectManager->get(\Magento\Catalog\Model\Product::class);
            $productModel = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
            foreach ($items as $item) {
                try {
                    /** @var Product $product */
                    // @todo reuse results from self::getContentIDs()
                    $product = $productModel->getById($item->getProductId());
                    $price = $priceHelper->currency($product->getFinalPrice(), false, false);
                    $content = '{id:"' . $product->getId() . '",quantity:' . (int)$item->getQtyOrdered()
                        . ',item_price:' . $price . '}';
                    $contents[] = $content;
                } catch (\Exception $e) {}
            }
        }
        return implode(',', $contents);
    }

    /**
     * @return string
     */
    public function getEventToObserveName()
    {
        return 'facebook_businessextension_ssapi_purchase';
    }
}
