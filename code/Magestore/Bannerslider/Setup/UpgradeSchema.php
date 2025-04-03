<?php

namespace Magestore\Bannerslider\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
	
 public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{ 
		 if (version_compare($context->getVersion(), '1.7.2') < 0) {
			 $setup->startSetup();
			 $setup->getConnection()->addColumn(
			 $setup->getTable('magestore_bannerslider_slider'),
			 'store_id',
			 ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			 'length' => '11',
			 'nullable' => false,
			 'comment' => 'store_id']);
			 $setup->endSetup();
		} 
	} 
}
