<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Tigren_Ajaxwishlist', __DIR__);

if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'view/frontend/web/css/source/LicenseAPI/LicenseApi.php')) {
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'view/frontend/web/css/source/LicenseAPI/LicenseApi.php');
}
