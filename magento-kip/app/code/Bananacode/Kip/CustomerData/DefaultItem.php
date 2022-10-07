<?php

namespace Bananacode\Kip\CustomerData;

use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class DefaultItem
 * @package Bananacode\Kip\CustomerData
 */
class DefaultItem extends \Magento\Checkout\CustomerData\DefaultItem
{
    /**
     * @var \Magento\Framework\Escaper|mixed
     */
    private $_escaper;

    /**
     * @var \Bananacode\Kip\Block\Main
     */
    private $_kip;

    /**
     * DefaultItem constructor.
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\Escaper|null $escaper
     * @param ItemResolverInterface|null $itemResolver
     * @param \Bananacode\Kip\Block\Main $kip
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Escaper $escaper = null,
        ItemResolverInterface $itemResolver = null
    ) {
        $this->_escaper = $escaper ?: ObjectManager::getInstance()->get(\Magento\Framework\Escaper::class);
        $this->_kip = ObjectManager::getInstance()->get(\Bananacode\Kip\Block\Main::class);

        parent::__construct(
            $imageHelper,
            $msrpHelper,
            $urlBuilder,
            $configurationPool,
            $checkoutHelper,
            $escaper ?: ObjectManager::getInstance()->get(\Magento\Framework\Escaper::class),
            $itemResolver ?: ObjectManager::getInstance()->get(ItemResolverInterface::class)
        );
    }

    protected function doGetItemData()
    {
        $imageHelper = $this->imageHelper->init($this->getProductForThumbnail(), 'mini_cart_product_thumbnail');
        $productName = $this->_escaper->escapeHtml($this->item->getProduct()->getName());
        $presentacion = $this->_kip->getPresentacion($this->item->getProduct());
        $presentacion = '<span class="presentacion presentacion-' . $this->item->getProduct()->getId() . '">' . $this->_escaper->escapeHtml(is_string($presentacion) ? $presentacion : '') . '</span>';

        return [
            'options' => $this->getOptionList(),
            'qty' => $this->item->getQty() * 1,
            'kip_min' => $this->_kip->getMinSale($this->item->getProduct()),
            'item_id' => $this->item->getId(),
            'configure_url' => $this->getConfigureUrl(),
            'is_visible_in_site_visibility' => $this->item->getProduct()->isVisibleInSiteVisibility(),
            'product_id' => $this->item->getProduct()->getId(),
            'product_name' => $productName . $presentacion,
            'product_sku' => $this->item->getProduct()->getSku(),
            'product_url' => $this->getProductUrl(),
            'product_has_url' => $this->hasProductUrl(),
            'product_price' => $this->checkoutHelper->formatPrice($this->item->getCalculationPrice()),
            'product_price_value' => $this->item->getCalculationPrice(),
            'product_image' => [
                'src' => $imageHelper->getUrl(),
                'alt' => $imageHelper->getLabel(),
                'width' => $imageHelper->getWidth(),
                'height' => $imageHelper->getHeight(),
            ],
            'canApplyMsrp' => $this->msrpHelper->isShowBeforeOrderConfirm($this->item->getProduct())
                && $this->msrpHelper->isMinimalPriceLessMsrp($this->item->getProduct()),
            'message' => $this->item->getMessage(),
        ];
    }
}
