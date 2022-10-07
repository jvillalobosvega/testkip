<?php

namespace Bananacode\App\Model\Resolver\DataProvider;

/**
 * Class ProductAttributesGraphql
 * @package Bananacode\App\Model\Resolver\DataProvider
 */
class ProductAttributesGraphql extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * ProductAttributesGraphql constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        array $data = []
    ) {
        $this->_productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    /**
     * @param $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductBySku($sku)
    {
        return $this->_productRepository->get($sku);
    }

    /**
     * @param $sku
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttributesBySku($sku)
    {
        $_product = $this->getProductBySku($sku);
        $_result = [];
        $i = 0;

        foreach ($_product->getAttributes() as $attribute) {
            if ($attribute->getIsUserDefined()) {
                $_result[$i] = [
                    "code" => $attribute->getAttributeCode(),
                    "label" => $attribute->getFrontend()->getLabel(),
                    "value" => $attribute->getFrontend()->getValue($_product),
                    "value_id" => $attribute->getBackend()->getValueId(),
                ];
            }
            $i++;
        }

        return $_result;
    }
}
