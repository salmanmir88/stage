<?php

namespace Evince\CourierManager\Plugin\Model\Customer\Address;

use Magento\Customer\Model\Address;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetCityPlugin
{
	public function afterGetDataModel(
        Address $subject,
        $addressDataObject
    ) 
    {
        echo "string";exit;
        $addressDataObject->setCity('Ilesh Test City');
        return $addressDataObject;
    }
}