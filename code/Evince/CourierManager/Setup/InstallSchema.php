<?php

namespace Evince\CourierManager\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $table = $setup->getConnection()->newTable(
            $setup->getTable('courier_manager')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Item ID'
        )->addColumn(
            'city',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'City'
        )->addColumn(
            'city_arabic',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'City Arabic'
        )->setComment(
            'Courier Management Table'
        );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
