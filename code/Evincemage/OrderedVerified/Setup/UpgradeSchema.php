<?php
namespace Evincemage\OrderedVerified\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{
		$connection = $setup->getConnection();
		$connection->addColumn(
			$setup->getTable('sales_order_grid'),
			'is_order_verified',
			[
				'type' => Table::TYPE_SMALLINT,
				'length' => 3,
				'nullable' => true,
				'default' => 0,
				'comment' => 'Is Order Verified'

			]
		);
	}
}