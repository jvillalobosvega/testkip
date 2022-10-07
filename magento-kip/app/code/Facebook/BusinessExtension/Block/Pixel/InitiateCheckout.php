<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 */

namespace Facebook\BusinessExtension\Block\Pixel;

class InitiateCheckout extends Common
{
    /**
     * @return string
     */
    public function getContentIDs()
    {
        $productIds = [];
        $cart = $this->fbeHelper->getObject(\Magento\Checkout\Model\Cart::class);
        $items = $cart->getQuote()->getAllVisibleItems();
        /**
         * @var $productModel \Magento\Catalog\Api\ProductRepositoryInterface
         */
        //$productModel = $this->objectManager->get(\Magento\Catalog\Model\Product::class);
        $productModel = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        foreach ($items as $item) {
            try {
                $product = $productModel->getById($item->getProductId());
                $productIds[] = $product->getId();
            } catch (\Exception $e) {

            }
        }
        return $this->arrayToCommaSeparatedStringValues($productIds);
    }

    public function getValue()
    {
        $cart = $this->fbeHelper->getObject(\Magento\Checkout\Model\Cart::class);
        if (!$cart || !$cart->getQuote()) {
            return null;
        }
        $subtotal = $cart->getQuote()->getSubtotal();
        if ($subtotal) {
            $priceHelper = $this->fbeHelper->getObject(\Magento\Framework\Pricing\Helper\Data::class);
            return $priceHelper->currency($subtotal, false, false);
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getContents()
    {
        $cart = $this->fbeHelper->getObject(\Magento\Checkout\Model\Cart::class);
        if (!$cart || !$cart->getQuote()) {
            return '';
        }
        $contents = [];
        $items = $cart->getQuote()->getAllVisibleItems();
        /**
         * @var $productModel \Magento\Catalog\Api\ProductRepositoryInterface
         */
        //$productModel = $this->objectManager->get(\Magento\Catalog\Model\Product::class);
        $productModel = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $priceHelper = $this->objectManager->get(\Magento\Framework\Pricing\Helper\Data::class);
        foreach ($items as $item) {
            try {
                $product = $productModel->getById($item->getProductId());
                $price = $priceHelper->currency($product->getFinalPrice(), false, false);
                $content = '{id:"' . $product->getId() . '",quantity:' . (int)$item->getQty()
                    . ',item_price:' . $price . "}";
                $contents[] = $content;
            } catch (\Exception $e) {

            }
        }
        return implode(',', $contents);
    }

    /**
     * @return int|null
     */
    public function getNumItems()
    {
        $cart = $this->fbeHelper->getObject(\Magento\Checkout\Model\Cart::class);
        if (!$cart || !$cart->getQuote()) {
            return null;
        }
        $numItems = 0;
        $items = $cart->getQuote()->getAllVisibleItems();
        foreach ($items as $item) {
            $numItems += $item->getQty();
        }
        return $numItems;
    }

    /**
     * @return string
     */
    public function getEventToObserveName()
    {
        return 'facebook_businessextension_ssapi_initiate_checkout';
    }
}
