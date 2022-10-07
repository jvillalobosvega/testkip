<?php

namespace Bananacode\Kip\Cron;

/**
 * Class LsVariant
 * @package Bananacode\Kip\Cron
 */
class LsVariant
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
     * @var \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory
     */
    protected $_optionValuesFactory;

    /**
     * LsNote constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $productCustomOption
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $_productLoader
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $productCustomOption,
        \Magento\Catalog\Api\ProductRepositoryInterface $_productLoader,
        \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory $optionValuesFactory
    ) {
        $this->_resourceConnection = $resourceConnection;
        $this->_productCustomOption = $productCustomOption;
        $this->_productLoader = $_productLoader;
        $this->_optionValuesFactory = $optionValuesFactory;

        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/bananacrons.log');
        $this->_logger = new \Monolog\Logger('Bananacrons');
        $this->_logger->pushHandler($logHandler);
    }

    /**
     * Check for products with "ls_variantN" and create customizable options
     */
    public function execute()
    {
        //disabled on crontab....

        $this->_logger->addInfo(print_r('------------------------------------', true));
        $this->_logger->addInfo(print_r('------------- LsVariant ------------', true));
        $this->_logger->addInfo(print_r('------------------------------------', true));

        $connection = $this->_resourceConnection->getConnection();

        $sql = "SELECT * FROM `eav_attribute` WHERE `attribute_code` = 'ls_opc_variante'";
        $attribute = $connection->fetchOne($sql);

        $sql = "SELECT * from eav_attribute_option where attribute_id = " . $attribute . ";";
        $options = $connection->fetchAll($sql);

        foreach ($options as $index => $option) {
            $sql = "SELECT * from catalog_product_entity_varchar where attribute_id = " . $attribute . " and value = " . $option['option_id'] . ";";
            $products = $connection->fetchAll($sql);

            $sql = "SELECT * from eav_attribute_option_value where option_id = " . $option['option_id'] . ";";
            $variant = $connection->fetchRow($sql);
            $variant = isset($variant['value']) ? str_replace('VARIANTE', '', $variant['value']) : null;
            if($variant) {
                $sql = "SELECT * FROM `eav_attribute` WHERE `attribute_code` like '%ls_variante" . $variant . "%'";
                $variant = $connection->fetchOne($sql);
                $sql = "SELECT * from eav_attribute_option where attribute_id = " . $variant . ";";
                $variantOpts = $connection->fetchAll($sql);
                $variantOptsValues = [];
                foreach ($variantOpts as $variantOpt) {
                    $sql = "SELECT * from eav_attribute_option_value where option_id = " . $variantOpt['option_id'] . ";";
                    $variantOptsValues[] = $connection->fetchRow($sql);
                }

                if(count($variantOptsValues) > 0) {
                    /*$label = $variantOptsValues[0];
                    $label = explode(' ', $label['value']);*/
                    $label = count([]) > 0 ? $label[0] : 'Opción ' . ($index + 1);
                    foreach ($products as $product) {
                        try {
                            $productEntity = $this->_productLoader->getById($product['entity_id']);
                            $productEntity->setOptions([]);
                            $this->_productLoader->save($productEntity);
                            continue;

                            $variantNReady = false;
                            foreach ($productEntity->getOptions() as $productOpt) {
                                if ($productOpt->getTitle() === $label) {
                                    $variantNReady = true;
                                }
                                $currentOptions[] = $productOpt;
                            }

                            if (!$variantNReady) {
                                /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $customOption */
                                $customOption = $this->_productCustomOption->create();
                                $customOption->setTitle($label)
                                    ->setType('drop_down')
                                    ->setIsRequire(false)
                                    ->setSortOrder(($index + 1))
                                    ->setPrice(0.00)
                                    ->setPriceType('fixed')
                                    ->setProductSku($productEntity->getSku());

                                $values = [];
                                foreach ($variantOptsValues as $i => $variantOptValue) {
                                    if ($variantOptValue/* && $i > 0*/) {
                                        $value = $this->_optionValuesFactory->create(['data' => [
                                            'title' => $variantOptValue['value'],
                                            'price' => '0',
                                            'price_type' => 'fixed',
                                            'sku' => $productEntity->getSku() . $variantOptValue['value_id'],
                                            'sort_order' => $i,
                                            'is_delete' => '0',
                                            'is_require' => 0,
                                        ]]);
                                        $values[] = $value;
                                    }
                                }

                                $customOption->setValues($values);
                                $currentOptions[] = $customOption;
                                $this->_logger->addInfo(print_r($productEntity->getName(), true));
                            }

                            $productEntity->setOptions($currentOptions);
                            $this->_productLoader->save($productEntity);
                        } catch (\Exception $e) {
                            $this->_logger->addError(print_r($e->getMessage(), true));
                        }
                    }
                }
            }
        }

        $this->_logger->addInfo(print_r('LsVariant ready...', true));
    }
}
