<?php

namespace Bananacode\Kipping\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Bananacode\Kipping\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();

        $connection = $installer->getConnection();

        /**
         * Quote Address
         */

        $connection->addColumn(
            $installer->getTable('quote_address'),
            'address_latitude',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'length' => 255,
                'comment' => 'Latitude'
            ]
        );

        $connection->addColumn(
            $installer->getTable('quote_address'),
            'address_longitude',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'length' => 255,
                'comment' => 'Longitude'
            ]
        );

        /**
         * Sales Order
         */

        $connection->addColumn(
            $installer->getTable('sales_order'),
            'address_latitude',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'length' => 255,
                'comment' => 'Latitude'
            ]
        );

        $connection->addColumn(
            $installer->getTable('sales_order'),
            'address_longitude',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'length' => 255,
                'comment' => 'Longitude'
            ]
        );

        $connection->addColumn(
            $installer->getTable('sales_order'),
            'kipping_delivery',
            [
                'type' => Table::TYPE_DATETIME,
                'nullable' => true,
                'default' => null,
                'comment' => 'Kipping Delivery Datetime'
            ]
        );


        $connection->addColumn(
            $installer->getTable('sales_order'),
            'coupon_code',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'comment' => 'Coupon code'
            ]
        );

        $installer->endSetup();
    }
}
