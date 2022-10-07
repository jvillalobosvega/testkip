<?php

namespace Bananacode\Kip\Cron;

/**
 * Class LsNote
 * @package Bananacode\Brand\Cron
 */
class LsNote
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory
     */
    protected $_productCustomOption;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productLoader;

    /**
     * @var \Monolog\Logger
     */
    protected $_logger;

    /**
     * LsNote constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $productCustomOption
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $_productLoader
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $productCustomOption,
        \Magento\Catalog\Api\ProductRepositoryInterface $_productLoader
    ) {
        $this->_resourceConnection = $resourceConnection;
        $this->_productCustomOption = $productCustomOption;
        $this->_productLoader = $_productLoader;

        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/bananacrons.log');
        $this->_logger = new \Monolog\Logger('Bananacrons');
        $this->_logger->pushHandler($logHandler);
    }

    /**
     * Check for products with "ls_note" = "sí" and set the note customizable option
     */
    public function execute()
    {
        $this->_logger->addInfo(print_r('---------------------------------', true));
        $this->_logger->addInfo(print_r('------------- LsNote ------------', true));
        $this->_logger->addInfo(print_r('---------------------------------', true));

        $connection = $this->_resourceConnection->getConnection();

        $sql = "SELECT * FROM `eav_attribute` WHERE `attribute_code` = 'ls_nota'";

        $attribute = $connection->fetchOne($sql);

        $sql = "select * from eav_attribute_option where attribute_id = " . $attribute . ";";

        $options = $connection->fetchAll($sql);

        $yes = null;
        foreach ($options as $option) {
            $sql = "select * from eav_attribute_option_value where option_id = " . $option['option_id'] . " and value = 'Sí';";

            if ($connection->fetchOne($sql)) {
                $yes = $option;
            }
        }

        if ($yes) {
            $sql = "select * from catalog_product_entity_varchar where attribute_id = " . $attribute . " and value = " . $yes['option_id'] . ";";

            $products = $connection->fetchAll($sql);

            foreach ($products as $product) {
                try {
                    /* @var \Magento\Catalog\Api\Data\ProductInterface $productEntity */
                    $productEntity = $this->_productLoader->getById($product['entity_id']);
                    $currentOptions = [];
                    $notaReady = false;
                    foreach ($productEntity->getOptions() as $option) {
                        if ($option->getTitle() === 'Nota') {
                            $notaReady = true;
                        }
                        $currentOptions[] = $option;
                    }
                    if (!$notaReady) {
                        /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $customOption */
                        $customOption = $this->_productCustomOption->create();

                        $customOption->setTitle('Nota')
                            ->setType('area')
                            ->setIsRequire(false)
                            ->setSortOrder(1)
                            ->setPrice(0.00)
                            ->setPriceType('fixed')
                            ->setMaxCharacters(140)
                            ->setProductSku($productEntity->getSku());
                        $currentOptions[] = $customOption;

                        $this->_logger->addInfo(print_r($productEntity->getName(), true));

                        $productEntity->setOptions($currentOptions);

                        $this->_productLoader->save($productEntity);
                    }
                } catch (\Exception $e) {
                    $this->_logger->addError(print_r($e->getMessage(), true));
                }
            }
        }

        $this->_logger->addInfo(print_r('LsNote ready...', true));
    }
}
