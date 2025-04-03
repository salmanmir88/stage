<?php

namespace Evince\CourierManager\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('courier_manager'),
                'courier',
                [
                    'type' => Table::TYPE_TEXT,
                    255,
                    'nullable' => true,
                    'comment' => 'courier'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('courier_manager'),
                'country_code',
                [
                    'type' => Table::TYPE_TEXT,
                    255,
                    'nullable' => true,
                    'comment' => 'country_code',
                    'after' => 'city'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('courier_manager'),
                'store_ids',
                [
                    'type' => Table::TYPE_TEXT,
                    255,
                    'nullable' => true,
                    'comment' => 'store view',
                    'after' => 'city'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('courier_manager'),
                'city_code',
                [
                    'type' => Table::TYPE_TEXT,
                    255,
                    'nullable' => true,
                    'comment' => 'code',
                    'after' => 'city'
                ]
            );
        }
        $setup->endSetup();
    }
}