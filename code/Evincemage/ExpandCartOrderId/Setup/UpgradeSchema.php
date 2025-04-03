<?php
namespace Evincemage\ExpandCartOrderId\Setup;

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
			$setup->getTable('sales_order'),
			'expand_cart_id',
			[
				'type' => Table::TYPE_TEXT,
				'length' => 255,
				'nullable' => true,
				'default' => null,
				'comment' => 'Expand Cart Order Id'

			]
		);
	}
}