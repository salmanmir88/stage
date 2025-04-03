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

use Mirasvit\Seo\Api\Service\TemplateEngineServiceInterface;
use Mirasvit\Seo\Service\TemplateEngine\TemplateProcessor;

class TemplateEngineService implements TemplateEngineServiceInterface
{
    /**
     * @var TemplateProcessor
     */
    private $templateProcessor;

    /**
     * TemplateEngineService constructor.
     * @param TemplateProcessor $templateProcessor
     */
    public function __construct(
        TemplateProcessor $templateProcessor
    ) {
        $this->templateProcessor = $templateProcessor;
    }

    /**
     * @param string $template
     * @param array $vars
     * @return string
     */
    public function render($template, array $vars = [])
    {
        return $this->templateProcessor->process($template, $vars);
    }

    /**
     * @return TemplateEngine\Data\AbstractData[]
     */
    public function getData()
    {
        return $this->templateProcessor->getData();
    }
}
