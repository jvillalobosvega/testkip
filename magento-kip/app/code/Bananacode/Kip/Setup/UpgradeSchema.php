<?php

namespace Bananacode\Kip\Setup;

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

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $connection->addColumn($setup->getTable('sales_order_address'),
                'address_latitude',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'length' => 255,
                    'comment' => 'Latitude'
                ]);

            $connection->addColumn($setup->getTable('sales_order_address'),
                'address_longitude',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'length' => 255,
                    'comment' => 'Longitude'
                ]);
        }

        $setup->endSetup();
    }
}
