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



namespace Mirasvit\SeoContent\Service;

use Mirasvit\Seo\Api\Service\StateServiceInterface;
use Mirasvit\Seo\Api\Service\TemplateEngineServiceInterface;
use Mirasvit\SeoContent\Api\Data\ContentInterface;
use Mirasvit\SeoContent\Api\Data\TemplateInterface;
use Mirasvit\SeoContent\Service\Content\Modifier\ModifierInterface;

class ContentService
{
    /**
     * @var bool
     */
    /**
     * @var bool
     */
    private $isProcessed = false;

    /**
     * @var TemplateService
     */
    /**
     * @var TemplateService
     */
    private $templateService;

    /**
     * @var RewriteService
     */
    /**
     * @var RewriteService
     */
    private $rewriteService;

    /**
     * @var StateServiceInterface
     */
    /**
     * @var StateServiceInterface
     */
    private $stateService;

    /**
     * @var ContentInterface
     */
    /**
     * @var ContentInterface
     */
    private $content;

    /**
     * @var TemplateEngineServiceInterface
     */
    /**
     * @var TemplateEngineServiceInterface
     */
    private $templateEngineService;

    /**
     * @var ModifierInterface[]
     */
    private $modifierPool;

    /**
     * ContentService constructor.
     *
     * @param TemplateService                $templateService
     * @param RewriteService                 $rewriteService
     * @param StateServiceInterface          $stateService
     * @param ContentInterface               $content
     * @param TemplateEngineServiceInterface $templateEngineService
     * @param array                          $modifierPool
     */
    public function __construct(
        TemplateService $templateService,
        RewriteService $rewriteService,
        StateServiceInterface $stateService,
        ContentInterface $content,
        TemplateEngineServiceInterface $templateEngineService,
        array $modifierPool
    ) {
        $this->templateService = $templateService;
        $this->rewriteService  = $rewriteService;
        $this->stateService    = $stateService;

        $this->content               = $content;
        $this->templateEngineService = $templateEngineService;

        $this->modifierPool = $modifierPool;
    }

    /**
     * @return bool
     */
    public function isProcessablePage()
    {
        if ($this->stateService->isCategoryPage()
            || $this->stateService->isProductPage()
            || $this->stateService->isCmsPage()
            || $this->rewriteService->getRewrite(null)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isHomePage()
    {
        return $this->stateService->isHomePage();
    }

    /**
     * @param array $meta
     */
    public function putDefaultMeta(array $meta)
    {
        foreach ($meta as $property => $value) {
            $this->content->setData($property, $this->escapeJS($value));
        }

        $this->isProcessed = false;
    }

    /**
     * @param bool $ruleType
     * @param bool $product
     *
     * @return ContentInterface
     */
    public function getCurrentContent($ruleType = false, $product = false)
    {
        if ($this->isProcessed) {
            return $this->content;
        }

        $this->content = $this->processCurrentContent($ruleType, $product);

        $this->isProcessed = true;

        return $this->content->setData($this->escapeJS($this->content->getData()));
    }

    /**
     * @param bool $ruleType
     * @param bool $product
     *
     * @return ContentInterface
     */
    public function processCurrentContent($ruleType = false, $product = false)
    {
        if (!$ruleType) {
            $ruleType = $this->getRuleType();
        }

        $template = $this->templateService->getTemplate(
            $ruleType,
            $this->stateService->getCategory(),
            ($product) ? $product : $this->stateService->getProduct(),
            $this->stateService->getFilters()
        );

        $rewrite = $this->rewriteService->getRewrite(null);

        if ($template) {
            if ($ruleType == TemplateInterface::RULE_TYPE_PAGE && $this->isHomePage() && !$template->isApplyForHomepage()) {
                return $this->content;
            }

            $this->content->setData(ContentInterface::DESCRIPTION_POSITION, $template->getDescriptionPosition());
            $this->content->setData(ContentInterface::DESCRIPTION_TEMPLATE, $template->getDescriptionTemplate());
            $this->content->setData(ContentInterface::APPLIED_TEMPLATE_ID, $template->getId());
        }

        if ($rewrite) {
            $this->content->setData(ContentInterface::APPLIED_REWRITE_ID, $rewrite->getId());
            $this->content->setData(ContentInterface::DESCRIPTION_POSITION, $rewrite->getDescriptionPosition());
            $this->content->setData(ContentInterface::DESCRIPTION_TEMPLATE, $rewrite->getDescriptionTemplate());

            if ($rewrite->getMetaRobots() && $rewrite->getMetaRobots() != '-') {
                $this->content->setData(ContentInterface::META_ROBOTS, $rewrite->getMetaRobots());
            }
        }

        $properties = [
            ContentInterface::TITLE,
            ContentInterface::META_TITLE,
            ContentInterface::META_KEYWORDS,
            ContentInterface::META_DESCRIPTION,
            ContentInterface::DESCRIPTION,
            ContentInterface::SHORT_DESCRIPTION,
            ContentInterface::FULL_DESCRIPTION,
            ContentInterface::CATEGORY_DESCRIPTION,
        ];

        foreach ($properties as $property) {
            $rewriteValue = $rewrite ? $rewrite->getData($property) : false;

            if ($rewriteValue) {
                $this->content->setData($property, $rewriteValue);

                $this->content->setData(
                    $property . '_TOOLBAR',
                    "Rewrite #{$rewrite->getId()}"
                );

                continue;
            }

            $templateValue = $template ? $template->getData($property) : false;

            if ($templateValue) {
                $this->content->setData($property, $templateValue);

                $this->content->setData(
                    $property . '_TOOLBAR',
                    "Template #{$template->getId()}"
                );
            }
        }

        foreach ($properties as $property) {
            $this->content->setData($property, $this->templateEngineService->render(
                $this->content->getData($property),
                ($product) ? ['product' => $product] : []
            ));
        }

        foreach ($this->modifierPool as $modifier) {
            $this->content = $modifier->modify($this->content);
        }

        return $this->content;
    }

    /**
     * @param array $data
     *
     * @return array|string|string[]|null
     */
    public function escapeJS($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    continue;
                }
                $data[$key] = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $value);
            }

            return $data;
        } else {
            return preg_replace('#<script(.*?)>(.*?)</script>#is', '', $data);
        }
    }

    /**
     * @return int
     */
    private function getRuleType()
    {
        if ($this->stateService->isProductPage()) {
            return TemplateInterface::RULE_TYPE_PRODUCT;
        } elseif ($this->stateService->isNavigationPage()) {
            return TemplateInterface::RULE_TYPE_NAVIGATION;
        } elseif ($this->stateService->isCategoryPage()) {
            return TemplateInterface::RULE_TYPE_CATEGORY;
        } elseif ($this->stateService->isCmsPage()) {
            return TemplateInterface::RULE_TYPE_PAGE;
        }

        return 0;
    }
}
