<?php

namespace Bananacode\Kipping\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class AddDeliveryInSalesOrderGrid
 * @package Bananacode\Kipping\Setup\Patch\Data
 */
class AddDeliveryInSalesOrderGrid implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * AddDeliveryInSalesOrderGrid constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '1.0.3';
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->addDeliveryInSalesOrderGrid();
        $this->addCouponCodeInSalesOrderGrid();
        $this->addQtyInOrderGrid();
        $this->addlatlng();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * addDeliveryInSalesOrderGrid
     */
    private function addDeliveryInSalesOrderGrid()
    {
        $connection       = $this->moduleDataSetup->getConnection();
        $sourceTable      = $this->moduleDataSetup->getTable('sales_order');
        $destinationTable = $this->moduleDataSetup->getTable('sales_order_grid');
        if ($connection->tableColumnExists($this->moduleDataSetup->getTable('sales_order_grid'), 'kipping_delivery') === true) {
            $connection->query(
                $connection->updateFromSelect(
                    $connection->select()
                        ->join(
                            $sourceTable,
                            sprintf('%s.entity_id = %s.entity_id', $destinationTable, $sourceTable),
                            'kipping_delivery'
                        ),
                    $destinationTable
                )
            );
        }
    }

    private function addCouponCodeInSalesOrderGrid()
    {
        $connection       = $this->moduleDataSetup->getConnection();
        $sourceTable      = $this->moduleDataSetup->getTable('sales_order');
        $destinationTable = $this->moduleDataSetup->getTable('sales_order_grid');
        if ($connection->tableColumnExists($this->moduleDataSetup->getTable('sales_order_grid'), 'coupon_code') === true) {
            $connection->query(
                $connection->updateFromSelect(
                    $connection->select()
                        ->join(
                            $sourceTable,
                            sprintf('%s.entity_id = %s.entity_id', $destinationTable, $sourceTable),
                            'coupon_code'
                        ),
                    $destinationTable
                )
            );
        }
    }



    private function addQtyInOrderGrid()
    {
        $connection       = $this->moduleDataSetup->getConnection();
        $sourceTable      = $this->moduleDataSetup->getTable('sales_order');
        $destinationTable = $this->moduleDataSetup->getTable('sales_order_grid');
        if ($connection->tableColumnExists($this->moduleDataSetup->getTable('sales_order_grid'), 'total_qty_ordered') === true) {
            $connection->query(
                $connection->updateFromSelect(
                    $connection->select()
                        ->join(
                            $sourceTable,
                            sprintf('%s.entity_id = %s.entity_id', $destinationTable, $sourceTable),
                            'total_qty_ordered'
                        ),
                    $destinationTable
                )
            );
        }
    }

     /**
     * addlat
     */
    private function addlatlng()
    {
        $connection       = $this->moduleDataSetup->getConnection();
        $sourceTable      = $this->moduleDataSetup->getTable('sales_order');
        $destinationTable = $this->moduleDataSetup->getTable('sales_order_grid');
        if ($connection->tableColumnExists($this->moduleDataSetup->getTable('sales_order_grid'), 'latlng') === true) {
            $connection->query(
                $connection->updateFromSelect(
                    $connection->select()
                        ->join(
                            $sourceTable,
                            sprintf('%s.entity_id = %s.entity_id', $destinationTable, $sourceTable),
                            ['latlng' => "CONCAT(address_latitude, ' ', address_longitude)"]
                        ),
                    $destinationTable
                )
            );
        }
    }




    private function addOrderCount($id)
    {
        $connection       = $this->moduleDataSetup->getConnection();
        $sourceTable      = $this->moduleDataSetup->getTable('sales_order');
        $destinationTable = $this->moduleDataSetup->getTable('sales_order_grid');
        if ($connection->tableColumnExists($this->moduleDataSetup->getTable('sales_order_grid'), 'latlng') === true) {
            $cn = $this->countOrders($id);
            $connection->query(
                $connection->updateFromSelect(
                    $connection->select()
                        ->join(
                            $sourceTable,
                            sprintf('%s.entity_id = %s.entity_id', $destinationTable, $sourceTable),
                            ['count_orders' => $cn ]
                        )->where(),
                    $destinationTable
                )
            );
        }
    }

    private function countOrders($id)
    {
        $select = $this->moduleDataSetup->getConnection()->select();
        $select->from(
            ['orders' => $this->moduleDataSetup->getTable('sales_order')],
            ['orders.entity_id']
        )
            ->where('orders.customer_id = ?', $id);
        $orders = $this->moduleDataSetup->getConnection()->fetchAll($select);
        return $orders;
    }




    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
