<?php
 
namespace Dakha\CustomWork\Block\Adminhtml\Form\Field;
 
use Magento\Framework\View\Element\Html\Select; 
/**
 * Class OrderOptionColumn
 * @package Dakha\CustomWork\Block\Adminhtml\Form\Field
 */
class OrderOptionColumn extends Select
{

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
 
 
    /**
     * @param $value
     * @return OrderOptionColumn
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }
 
 
    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }
 
 
    /**
     * @return array
     */
    private function getSourceOptions()
    {
        $optionArr = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $optionData = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory')->create()->toOptionArray();
        foreach ($optionData as $option) {
            $optionArr[] = ['label' => $option['label'], 'value' => $option['value']];
        }
        \Magento\Framework\App\ObjectManager::getInstance()
       ->get(\Psr\Log\LoggerInterface::class)->info(print_r($optionArr,true));

        return $optionArr;
    }
}
