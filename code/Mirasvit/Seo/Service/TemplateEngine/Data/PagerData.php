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



namespace Mirasvit\Seo\Service\TemplateEngine\Data;

use Magento\Framework\App\RequestInterface;

class PagerData extends AbstractData
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * PagerData constructor.
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;

        parent::__construct();
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTitle()
    {
        return __('Pagination');
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return [
            'page',
        ];
    }

    /**
     * @param string $attribute
     * @param array $additionalData
     * @return bool|false|\Magento\Framework\Phrase|string
     */
    public function getValue($attribute, $additionalData = [])
    {
        $page = (int)$this->request->getParam('p');

        switch ($attribute) {
            case 'page':
                return $page > 1 ? __('Page %1', $page) : false;
        }

        return false;
    }
}
