<?php

namespace Developerswing\OrderTracking\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Setup\Exception;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup; 
        $installer->startSetup();
        try {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('track_order_status_dates')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                ['nullable' => false],
                'order id'
            )->addColumn(
                'shipping_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                ['nullable' => false],
                'Shipping status'
            )->addColumn(
                'customs_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                ['nullable' => false],
                'Customs status'
            )->addColumn(
                'with_courier_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                ['nullable' => false],
                'With courier status'
            )->addColumn(
                'delivered_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                ['nullable' => false],
                'Delivered status'
            )->setComment(
                'Track order status dates'
            );

            $installer->getConnection()->createTable($table);
            $installer->endSetup();
        } catch (Exception $err) {
            \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info($err->getMessage());
        }
    }
}