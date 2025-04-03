<?php

namespace Xigen\CsvUpload\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Xigen CSV setup InstallSchema class
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $table_xigen_csvupload_csv = $setup->getConnection()->newTable($setup->getTable('xigen_csvupload_csv'));

        $table_xigen_csvupload_csv->addColumn(
            'csv_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true],
            'Entity ID'
        );
        $table_xigen_csvupload_csv->addColumn(
            'rulename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'rulename'
        );

        $table_xigen_csvupload_csv->addColumn(
            'filename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Filename'
        );

        $table_xigen_csvupload_csv->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Created At'
        );
        $table_xigen_csvupload_csv->addColumn(
            'applied_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Applied At'
        );
        $table_xigen_csvupload_csv->addColumn(
            'locked',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'locked'
        );
        $table_xigen_csvupload_csv->addColumn(
            'processed',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'processed'
        );
        $table_xigen_csvupload_csv->addColumn(
            'locked_to_remove',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Locked To Remove'
        );
        $table_xigen_csvupload_csv->addColumn(
            'job_in_process',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Cron Job In Process'
        );

        $setup->getConnection()->createTable($table_xigen_csvupload_csv);
    }
}
