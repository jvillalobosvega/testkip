<?php

namespace Bananacode\Kip\Cron;

/**
 * Class LsUnidadMin
 * @package Bananacode\Kip\Cron
 */
class LsUnidadMin
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
     * Check for products with "ls_decimal" = "sí" and allow decimals
     */
    public function execute()
    {
        $this->_logger->addInfo(print_r('---------------------------------', true));
        $this->_logger->addInfo(print_r('----------- LsUnidadMin ---------', true));
        $this->_logger->addInfo(print_r('---------------------------------', true));

        $this->checkDecimalMin();

        $this->enableDecimalAll();

        $this->_logger->addInfo(print_r('LsUnidadMin ready...', true));
    }

    /**
     *
     */
    private function checkDecimalMin() {
        $this->_logger->addInfo(print_r('----------- checkDecimalMin ---------', true));

        $connection = $this->_resourceConnection->getConnection();

        $sql = "SELECT * FROM `eav_attribute` WHERE `attribute_code` = 'ls_un_min_venta'";

        $attribute = $connection->fetchOne($sql);

        $sql = "SELECT * from catalog_product_entity_varchar where attribute_id = '" . $attribute ."' order by value;";

        $products = $connection->fetchAll($sql);

        foreach ($products as $product) {
            try {
                $val = floatval($product['value']);
                if(is_numeric($val)/* && floor($val) != $val*/) {

                    $sql = "SELECT min_sale_qty from `cataloginventory_stock_item`
                            WHERE `product_id` = " . $product['entity_id'] . ";";

                    $currentMinSaleQty = $connection->fetchAll($sql);
                    if(count($currentMinSaleQty)) {
                        $currentMinSaleQty = floatval($currentMinSaleQty[0]['min_sale_qty']);
                        if($currentMinSaleQty == $val) {
                            $this->_logger->addInfo(print_r("Continue...{$product['entity_id']}:{$currentMinSaleQty}:{$val}", true));
                            continue;
                        }
                    }

                    $sql = "UPDATE `cataloginventory_stock_item`
                            SET use_config_min_sale_qty = 0, `min_sale_qty` = "  . $val . "
                            WHERE `product_id` = " . $product['entity_id'] . ";";

                    $connection->query($sql);

                    $productModel = $this->_productLoader->getById($product['entity_id']);

                    if ($productModel->getTypeId() == "configurable") {
                        $children = $productModel->getTypeInstance()->getUsedProducts($productModel);
                        foreach ($children as $child){
                            $sqlChild = "UPDATE `cataloginventory_stock_item`
                            SET use_config_min_sale_qty = 0, `min_sale_qty` = "  . $val . "
                            WHERE `product_id` = " . $child->getID() . ";";

                            $connection->query($sqlChild);

                            $childModel = $this->_productLoader->getById($child->getID());

                            $this->_productLoader->save($childModel);
                        }
                    }

                    $this->_logger->addInfo(print_r($productModel->getName(), true));

                    $this->_productLoader->save($productModel);
                }
            } catch (\Exception $e) {
                $this->_logger->addInfo(print_r($e->getMessage(), true));
            }
        }
    }

    /**
     *
     */
    private function enableDecimalAll() {
        $this->_logger->addInfo(print_r('----------- enableDecimalAll ---------', true));

        $connection = $this->_resourceConnection->getConnection();

        $sql = "SELECT * FROM `cataloginventory_stock_item` WHERE `is_qty_decimal` != 1;";

        $products = $connection->fetchAll($sql);

        foreach ($products as $product) {
            try {
                $sql = "UPDATE `cataloginventory_stock_item`
                        SET `is_qty_decimal` = 1
                        WHERE `product_id` = " . $product['product_id'] . ";";

                $connection->query($sql);

                /* @var \Magento\Catalog\Api\Data\ProductInterface $productModel */
                $productModel = $this->_productLoader->getById($product['product_id']);

                $this->_logger->addInfo(print_r($productModel->getName(), true));

                $this->_productLoader->save($productModel);
            } catch (\Exception $e) {
                $this->_logger->addInfo(print_r($e->getMessage(), true));
            }
        }
    }
}
