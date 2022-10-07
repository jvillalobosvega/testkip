<?php

namespace Bananacode\Kip\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class FacebookProductsFeeds
 * @package Bananacode\Kip\Cron
 */
class FacebookProductsFeeds
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Monolog\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_productStatus;

    /**
     * @var \Magento\Framework\Url
     */
    protected $_url;

    /**
     * @var \Bananacode\Kip\Block\Main
     */
    protected $_kip;

    /**
     * UpdateProductsFeeds constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Url $url
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url $url,
        \Bananacode\Kip\Block\Main $kip
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_fileSystem = $filesystem;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_storeManager = $storeManager;
        $this->_productStatus = $productStatus;
        $this->_url = $url;
        $this->_kip = $kip;

        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/bananacrons.log');
        $this->_logger = new \Monolog\Logger('Bananacrons');
        $this->_logger->pushHandler($logHandler);
    }

    /**
     * Send notifications to brands of pending orders
     */
    public function execute()
    {
        try {
            $this->_logger->addInfo(print_r('---------------------------------', true));
            $this->_logger->addInfo(print_r('----- FacebookProductsFeeds -----', true));
            $this->_logger->addInfo(print_r('---------------------------------', true));

            $directory = $this->_fileSystem->getDirectoryWrite(DirectoryList::PUB);
            $collection = $this->_productCollectionFactory->create();
            $collection
                ->addAttributeToSelect('*')
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addAttributeToFilter('visibility', ['in' => $this->_catalogProductVisibility->getVisibleInSiteIds()]);

            $name = 'kip';
            $filepath = 'feeds/' . $name . 'ProductFeed.csv';
            $stream = $directory->openFile($filepath, 'w+');
            $stream->lock();
            $stream->writeCsv([
                'id',
                'title',
                'description',
                'availability',
                'condition',
                'price',
                'link',
                'image_link',
                'brand',
            ]);

            /**
             * @var $product \Magento\Catalog\Model\Product
             */
            foreach ($collection as $product) {
                if($product->getTypeId() == "bundle" || $product->getTypeId() == "grouped") {
                    continue;
                }

                if ($product->getTypeId() == "configurable") {
                    $price = 0;
                    $children = $product->getTypeInstance()->getUsedProducts($product);
                    if (count($children) > 0) {
                        $price = $children[0]->getPrice();
                        foreach ($children as $child) {
                            if ($child->getPrice() < $price) {
                                $price = $child->getPrice();
                            }
                        }
                    }
                } else {
                    $price = $product->getPrice();
                }

                $brand = $product->getCustomAttribute('ls_marca');
                $url = $this->_url->getUrl('catalog/product/view', ['id' => $product->getId(), '_nosid' => true, '_query' => ['___store' => 'default']]);
                $presentacion = strip_tags($this->_kip->getPresentacion($product, true));
                $image = $this->getBaseMediaUrl() . '/catalog/product' . $product->getData('image');
                if($brand && $price > 0) {
                    $price = number_format((float)$price, 2, '.', '') . ' ' . $this->_storeManager->getStore()->getCurrentCurrencyCode();
                    if(
                        !empty($product->getSku()) &&
                        !empty($product->getName()) &&
                        !empty($presentacion) &&
                        !empty($price) &&
                        !empty($url) &&
                        !empty($image) &&
                        is_numeric($product->getSku())
                    ) {
                        $productData = [];
                        $productData[] = $product->getSku();
                        $productData[] = $product->getName();
                        $productData[] = $presentacion;
                        $productData[] = $product->isAvailable() ? 'in stock' : 'out of stock';
                        $productData[] = 'new';
                        $productData[] = $price;
                        $productData[] = $url;
                        $productData[] = $image;
                        $productData[] = $brand->getValue();
                        $this->_logger->addInfo(print_r($product->getName(), true));
                        $stream->writeCsv($productData);
                    }
                }
            }

        } catch (\Exception $e) {
            $this->_logger->addError(print_r($e->getMessage(), true));
        }
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getBaseMediaUrl()
    {
        return rtrim($this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA), '/');
    }
}
