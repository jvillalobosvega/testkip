<?php

namespace Bananacode\Kip\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Recirculation
 * @package Bananacode\Kip\Helper
 */
class Recirculation extends AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Recirculation constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        parent::__construct($context);
    }

    /**
     * @param $product \Magento\Catalog\Model\Product
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getRecirculationItems($product)
    {
        try {
            if ($product) {
                if ($product->getId()) {
                    $collection = $this->_productCollectionFactory->create();
                    $collection
                        ->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
                        ->addAttributeToSelect('*')
                        ->addCategoriesFilter(['in' => $product->getCategoryIds()])
                        ->setPageSize(10)
                        ->getSelect()
                        ->orderRand();

                    return $collection;
                }
            }
        } catch (\Exception $e) {
            return [];
        }
        return [];
    }

    /**
     * @param $config
     * @return mixed
     */
    public function getConfig($config)
    {
        return $this->scopeConfig->getValue(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
