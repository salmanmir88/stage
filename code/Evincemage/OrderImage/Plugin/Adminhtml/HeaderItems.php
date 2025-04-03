<?php 
namespace Evincemage\OrderImage\Plugin\Adminhtml;

class HeaderItems
{
	public function afterGetColumns(\Magento\Sales\Block\Adminhtml\Order\View\Items $subject, $result)
	{
		if(!empty($result)&&is_array($result))
		{
			array_unshift($result,__('Image'));
			return $result;
		}

		return $result;
	}
}