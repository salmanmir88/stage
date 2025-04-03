<?php 
namespace Eextensions\CustomOrderTab\Setup;


use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\InstallSchemaInterface;

class InstallSchema implements InstallSchemaInterface
{
	/**
     * @var array The attributes backend tables definitions.
     */
    private $backendTypes = [
        'datetime' => ['value', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, [], 'Value'],
        'decimal'  => ['value', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '12,4', [], 'Value'],
        'int'      => ['value', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Value'],
        'text'     => ['value', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'Value'],
        'varchar'  => ['value', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [], 'Value'],
    ];

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $table_eextensions_order_custom_comment = $setup->getConnection()->newTable($setup->getTable('eextensions_order_custom_comment'));

        
        $table_eextensions_order_custom_comment->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            array('identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,),
            'Entity ID'
        );
        
        $table_eextensions_order_custom_comment->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            null,
            [],
            'order_id'
        );
		
        $table_eextensions_order_custom_comment->addColumn(
            'increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            null,
            [],
            'increment_id'
        );
        
        $table_eextensions_order_custom_comment->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'title'
        );
        
        $table_eextensions_order_custom_comment->addColumn(
            'user_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'user_id'
        );
	

        $table_eextensions_order_custom_comment->addColumn(
            'user_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'user_email'
        );
        
        $table_eextensions_order_custom_comment->addColumn(
            'comment',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'comment'
        );
		
		$table_eextensions_order_custom_comment->addColumn(
			'created_at',
			\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
			null,
			['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
			'Created At'
		);

		$table_eextensions_order_custom_comment->addColumn(
			'updated_at',
			\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
			null,
			['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
			'Updated At')
		->setComment('Post Table');
        
        $setup->getConnection()->createTable($table_eextensions_order_custom_comment);

        $setup->endSetup();
    }
}
