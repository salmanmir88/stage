<?php
namespace Evincemage\OrderedVerified\Helper;
use Magento\Framework\App\ResourceConnection;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	public function __construct
	(
		\Magento\Framework\App\Helper\Context $context,
		ResourceConnection $resourceConnection
	)
	{
		$this->resourceConnection = $resourceConnection;	
		parent::__construct($context);
	}

	public function getIsOTPVerified($order_inc_id)
	{	
		if(empty($order_inc_id))
		{
			return false;
		}		
		$connection = $this->resourceConnection->getConnection();
		$table = $connection->getTableName('sales_order_grid');
		$query = "SELECT `is_order_verified` FROM `" . $table . "` WHERE increment_id =  $order_inc_id";
        $result = $connection->fetchOne($query);
        if($result=='1')
        {
        	return true;
        }
        else
        {
        	return false;	
        }

        return false;
	}
}