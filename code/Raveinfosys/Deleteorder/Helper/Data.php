<?php
namespace Raveinfosys\Deleteorder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

    const XML_PATH_ORDER_STATUS = 'deleteorder/general/order_status';

    public function getAllowedOrderStatus()
    {
        $statuses = $this->scopeConfig->getValue(
            self::XML_PATH_ORDER_STATUS,
            ScopeInterface::SCOPE_STORE
        );
        $allowedStatus = explode(",", $statuses);
        return $allowedStatus;
    }
}
