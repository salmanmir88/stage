<?php 
namespace Evincemage\CustomOrderStatus\Setup;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;


class InstallData implements InstallDataInterface
{
	const ORDER_STATE_CUSTOM_CODE = 'shipped_out';
	const ORDER_STATUS_CUSTOM_CODE = 'shipped_out';
	const ORDER_STATUS_CUSTOM_LABEL = 'Shipped Out';
    const STATE_NEW = 'new';
    const STATE_PENDING_PAYMENT = 'pending_payment';
    const STATE_PROCESSING = 'processing';
    const STATE_COMPLETE = 'complete';
    const STATE_CLOSED = 'closed';
    const STATE_CANCELED = 'canceled';
    const STATE_HOLDED = 'holded';
    const STATE_PAYMENT_REVIEW = 'payment_review';

    

	protected $statusFactory;
    /**
     * Status Resource Factory
     *
     * @var StatusResourceFactory
     */
    protected $statusResourceFactory;

    public function __construct
    (
    	StatusFactory $statusFactory,
    	StatusResourceFactory $statusResourceFactory
    )
    {
    	$this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;	
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $restStates = [
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'waiting_confirmation','status_label' => __('Waiting For Confirmation')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'confirmation_done','status_label' => __('THE ORDER HAS BEEN CONFIRMED')],    
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'not_available_factory','status_label' => __('Not Available From Factory')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'pre_order','status_label' => __('Pre Order')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'transit_korea','status_label' => __('Transit Korea')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'processing_under','status_label' => __('Processing is Underway')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'processing_progress','status_label' => __('Processing in Progress')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'transit','status_label' => __('Transit')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'transit_saudi','status_label' => __('Transit Saudi')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'ready_ship','status_label' => __('Ready To Ship')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'shipped_out','status_label' => __('Shipped Out')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'delayed','status_label' => __('Delayed')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'fal_issue','status_label' => __('Fal Issue')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'transit_jeddah','status_label' => __('Transit Jeddah')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'suspended','status_label' => __('Suspended')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'repeated','status_label' => __('Repeated')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'delivery_scheduled','status_label' => __('Delivery Scheduled')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'denied','status_label' => __('Denied')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'expired','status_label' => __('Expired')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'fetchr_ship','status_label' => __('Fetchr Shipping')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'fetchr_held','status_label' => __('Held at Fetchr')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'ready_pickup','status_label' => __('Ready For Pickup')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'return_custom','status_label' => __('Return')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'reversed_custom','status_label' => __('Reversed')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'shedule_delivery','status_label' => __('Schedule For Delivery')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'shipped_custom','status_label' => __('Shipped')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'prep_aramex','status_label' => __('Prepared For Aramex')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'prep_jeddah','status_label' => __('Preparation for Jeddah')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'sms_install','status_label' => __('SMSA has been installed')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'ship_via_courier','status_label' => __('Shipping Was Via Courier')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'ship_via_fall','status_label' => __('Shipping Was Via Fall')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'shipped_riyadh','status_label' => __('Shipped To Riyadh')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'shipping_mecca','status_label' => __('Shipping To Mecca')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'shipped_via_aramex','status_label' => __('The Order was Shipped via Aramex')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'international_ship','status_label' => __('International Shipment')],
        ['state_code' => self::STATE_PROCESSING,'status_code'=>'for_courier','status_label' => __('For a Courier')],

    ];
        foreach ($restStates as $customNewState)
        {
            $statusCode = $customNewState['status_code'];
            $statusLabel = $customNewState['status_label'];
            $stateCode = $customNewState['state_code'];
            $this->addNewOrderStatusToExistingState($statusCode,$statusLabel,$stateCode);
            //$this->addNewOrderStateAndStatus($statusCode,$statusLabel,$stateCode);
        }
    	
    }

    protected function addNewOrderStateAndStatus($cstatusCode,$cstatLabel,$cstateCode)
    {
    	$statusResource = $this->statusResourceFactory->create();
    	$status = $this->statusFactory->create();
    	$status->setData([
            'status' => $cstatusCode,
            'label' => $cstatLabel,
        ]);

        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {
            return;
        }

        $status->assignState($cstateCode, true, true);
    }

    protected function addNewOrderStatusToExistingState($cstatusCode,$cstatLabel,$cstateCodex)
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();
        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => $cstatusCode,
            'label' => $cstatLabel,
        ]);
        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {
            return;
        }
        $status->assignState($cstateCodex, false, true);
    }
}
