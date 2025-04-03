<?php

namespace Vnecoms\Sms\Setup;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    public function upgrade(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();
        $installer = $setup;
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            /*Add new column to customer_entity*/
            $setup->getConnection()->addColumn(
                $setup->getTable('customer_entity'),
                'mobilenumber',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'LENGTH' =>255,
                    'after' => 'gender',
                    'comment' => 'Customer Mobile Number'
                ]
            );
            
        }
        if (version_compare($context->getVersion(), '2.0.5') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('ves_sms_message'),
                'note',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'after' => 'to_mobile',
                    'comment' => 'Note'
                ]
            );
        }
        
        if (version_compare($context->getVersion(), '2.0.6') < 0) {
            /**
             * Create table 'ves_sms_block_list'
             */
            $table = $installer->getConnection()->newTable(
                $installer->getTable('ves_sms_block_list')
            )->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Id'
            )->addColumn(
                'rule',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false,],
                'Rule'
            )->addColumn(
                'note',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true,],
                'Note'
            )->setComment(
                'Message history'
            );
            $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '2.0.8') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('ves_sms_customer_mobile'),
                'additional_data',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'after' => 'status',
                    'comment' => 'Additional Data'
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.9') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'otp_verified',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Is OTP Verified'
                ]
            );
        }
    }
}
