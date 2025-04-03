<?php
namespace Evince\CourierManager\Model\Source;
use Magento\Framework\Option\ArrayInterface;

class Stores implements ArrayInterface
{
	public function toOptionArray()
	{
		$result = [['value' => 1, 'label' => __('English')],['value' => 2, 'label' => __('Arabic')]];

		return $result;

	}
}