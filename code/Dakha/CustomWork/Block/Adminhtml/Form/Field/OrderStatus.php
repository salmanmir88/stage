<?php
 
namespace Dakha\CustomWork\Block\Adminhtml\Form\Field;
 
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;  
/**
 * Class Range
 * @package Dakha\CustomWork\Block\Adminhtml\Form\Field
 */
class OrderStatus extends AbstractFieldArray
{
 
    /**
     * @var
     */
    protected $startTimeRenderer;
 
 
    /**
     * @var
     */
    protected $endTimeRenderer;
 
 
    /**
     *
     */
    protected function _prepareToRender()
    {
        $this->addColumn('order_status', [
            'label' => __('Status'),
            'renderer' => $this->getOrderStatusRenderer()
        ]);
        
        
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
 
 
    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws LocalizedException
     */
    private function getOrderStatusRenderer()
    {
        if (!$this->startTimeRenderer) {
            $this->startTimeRenderer = $this->getLayout()->createBlock(
                OrderOptionColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->startTimeRenderer->setClass('custom_start_range required-entry');
            $this->startTimeRenderer->setExtraParams('style="width:110px"');
        }
        return $this->startTimeRenderer;
    }
 
    /**
     * @param DataObject $row
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];
        $startTime = $row->getStartTime();
        if ($startTime !== null) {
            $options['option_' . $this->getOrderStatusRenderer()->calcOptionHash($startTime)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}