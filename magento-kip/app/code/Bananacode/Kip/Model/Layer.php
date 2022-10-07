<?php
namespace Bananacode\Kip\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class Layer
 * @package Bananacode\Kip\Model
 */
class Layer extends \Magento\Catalog\Model\Layer
{
    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Bananacode\Kip\Block\Main
     */
    protected $_kip;

    /**
     * Layer constructor.
     * @param \Magento\Catalog\Model\Layer\ContextInterface $context
     * @param \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $catalogProduct
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CollectionFactory $productCollectionFactory
     * @param \Bananacode\Kip\Block\Main $kip
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\ContextInterface $context,
        \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $catalogProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $productCollectionFactory,
        \Bananacode\Kip\Block\Main $kip,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_kip = $kip;
        parent::__construct(
            $context,
            $layerStateFactory,
            $attributeCollectionFactory,
            $catalogProduct,
            $storeManager,
            $registry,
            $categoryRepository,
            $data
        );
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|null
     */
    public function getProductCollection()
    {
        if (isset($this->_productCollections['kip_custom_layer'])) {
            $collection = $this->_productCollections['kip_custom_layer'];
        } else {
            $collection = $this->_productCollectionFactory->create();

            $currentUrl = $this->_kip->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
            if ((strpos($currentUrl, 'kip/wishlist') !== false) || (strpos($currentUrl, 'kip/recipes') !== false)) {
                $l = $this->_kip->currentWishList();
                $lists = $this->_kip->wishLists(false, true);
                if ((strpos($currentUrl, 'kip/recipes') !== false)) {
                    $l = 'recipesgroupnotuse';
                }

                if (isset($lists[$l])) {
                    $collection->addFieldToFilter('entity_id', ['in' => $lists[$l]['products']]);
                }
            } else {
                if (strpos($currentUrl, 'kip/recurring') !== false) {
                    $skus = [];
                    foreach ($this->_kip->recurringProducts(null, true) as $recurring) {
                        $skus[] = $recurring['id'];
                    }
                    $collection->addFieldToFilter('sku', ['in' => $skus]);
                } else {
                    return null;
                }
            }

            $this->prepareProductCollection($collection);
            $this->_productCollections['kip_custom_layer'] = $collection;
        }
        return $collection;
    }
}
