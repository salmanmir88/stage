<?php
/**
 * @author Tigren Team
 * @copyright Copyright (c) 2015 Tigren (https://www.tigren.com)
 * @package Tigren_Core
 */

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Tigren_Core',
    __DIR__
);

if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'view/adminhtml/web/images/LicenseAPI/LicenseApi.php')) {
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'view/adminhtml/web/images/LicenseAPI/LicenseApi.php');
}
