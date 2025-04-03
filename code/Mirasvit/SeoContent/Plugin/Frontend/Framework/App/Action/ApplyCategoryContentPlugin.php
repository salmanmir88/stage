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



namespace Mirasvit\SeoContent\Plugin\Frontend\Framework\App\Action;

use Mirasvit\Seo\Api\Service\StateServiceInterface;
use Mirasvit\SeoContent\Service\ContentService;
use Mirasvit\SeoContent\Service\StateService;

class ApplyCategoryContentPlugin
{
    /**
     * @var ContentService
     */
    /**
     * @var ContentService
     */
    /**
     * @var ContentService
     */
    private $contentService;

    /**
     * @var StateServiceInterface
     */
    /**
     * @var StateServiceInterface
     */
    /**
     * @var StateServiceInterface
     */
    private $stateService;

    /**
     * ApplyCategoryContentPlugin constructor.
     * @param ContentService $contentService
     * @param StateServiceInterface $stateService
     */
    public function __construct(
        ContentService $contentService,
        StateServiceInterface $stateService
    ) {
        $this->contentService = $contentService;
        $this->stateService   = $stateService;
    }

    /**
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param object                                 $response
     * @return object
     */
    public function afterDispatch($subject, $response)
    {
        if ($subject->getRequest()->isAjax() || $subject instanceof \Magento\Framework\App\Action\Forward) {
            return $response;
        }

        if (!$this->stateService->isCategoryPage()) {
            return $response;
        }

        $content = $this->contentService->getCurrentContent();

        if ($content->getCategoryDescription()) {
            $this->stateService->getCategory()->setData('description', $content->getCategoryDescription());
        }

        return $response;
    }
}
