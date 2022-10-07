<?php

namespace Bananacode\Kipping\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Bananacode\Kip\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $connection->addColumn($setup->getTable('sales_order_grid'),
                'kipping_delivery',
                [
                    'type' => Table::TYPE_DATETIME,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Kipping Delivery Datetime'
                ]
            );            
            $connection->addColumn($setup->getTable('sales_order_grid'),
                'coupon_code',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Coupon Code'
                ]
            );
            $connection->addColumn($setup->getTable('sales_order_grid'),
                'latlng',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'LatLng'
                ]
            );

            $connection->addColumn($setup->getTable('sales_order_grid'),
                'count_orders',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Cnt.Orders'
                ]
            );
            $connection->addColumn($setup->getTable('sales_order_grid'),
                'total_qty_ordered',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Total Qty. Ordered'
                ]
            );
        }
        

        $setup->endSetup();
    }
}
