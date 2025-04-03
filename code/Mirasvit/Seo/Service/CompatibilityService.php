<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-seo
 * @version   2.1.11
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Service;

use Mirasvit\Seo\Api\Service\CompatibilityServiceInterface;
use Mirasvit\Seo\Helper\Version;
use Mirasvit\Seo\Helper\Serializer;

/**
 * M2.2. compatibility
 */
class CompatibilityService implements CompatibilityServiceInterface
{
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var Version
     */
    private $version;

    /**
     * @param Version $version
     * @param Serializer $serializer
     */
    public function __construct(
        Version $version,
        Serializer $serializer
    ) {
        $this->version = $version;
        $this->serializer = $serializer;
    }

    /**
     * Prepare Rule Data For Save ('conditions_serialized', 'actions_serialized')
     *
     * {@inheritdoc}
     */
    public function prepareRuleDataForSave($value)
    {
        if ($this->version->getVersion() >= '2.2.0'
            && !json_decode($value)) {
            $value = $this->serializer->unserialize($value);
            $value = $this->serializer->serialize($value);
        }

        return $value;
    }
}
