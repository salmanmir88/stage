<?php

namespace Evince\CustomerResetPassword\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $setup->startSetup();

            $setup->getConnection()->addColumn(
                $setup->getTable('customer_entity'),
                'migrate_customer',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '5',
                    'nullable' => true,
                    'default' => NULL,
                    'comment' => 'Migrate Customer'
                ]
            );

            $setup->endSetup();
        }
    }
}