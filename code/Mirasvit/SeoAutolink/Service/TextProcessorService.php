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



namespace Mirasvit\SeoAutolink\Service;

use Mirasvit\SeoAutolink\Model\Config\Source\Occurence;
use Mirasvit\SeoAutolink\Model\Link;
use Mirasvit\SeoAutolink\Service\TextProcessor\Strings;
use Mirasvit\SeoAutolink\Service\TextProcessor\TextPlaceholder;

class TextProcessorService
{
    /**
     * @var \Mirasvit\SeoAutolink\Model\LinkFactory
     */
    private $linkFactory;

    /**
     * @var \Mirasvit\SeoAutolink\Model\Config
     */
    private $config;

    /**
     * @var \Mirasvit\Core\Api\TextHelperInterface
     */
    private $coreString;

    /**
     * @var \Mirasvit\SeoAutolink\Helper\Pattern
     */
    private $seoAutolinkPattern;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    const MAX_NUMBER = 999999;

    /**
     * @var bool
     */
    protected $_isSkipLinks;

    /**
     * @var int
     */
    protected $_sizeExplode = 0;

    /**
     * @var bool
     */
    protected $_isExcludedTags = true;

    /**
     * @var array
     */
    protected $_replacementsCountGlobal = [];

    /**
     * @var int
     */
    protected $currentNumberOfLinks = 0;

    /**
     * @var array
     */
    private $cache                = [];

    /**
     * TextProcessorService constructor.
     * @param \Mirasvit\SeoAutolink\Model\LinkFactory $linkFactory
     * @param \Mirasvit\SeoAutolink\Model\Config $config
     * @param \Mirasvit\Core\Api\TextHelperInterface $coreString
     * @param \Mirasvit\SeoAutolink\Helper\Pattern $seoAutolinkPattern
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Mirasvit\SeoAutolink\Model\LinkFactory $linkFactory,
        \Mirasvit\SeoAutolink\Model\Config $config,
        \Mirasvit\Core\Api\TextHelperInterface $coreString,
        \Mirasvit\SeoAutolink\Helper\Pattern $seoAutolinkPattern,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry
    ) {
        $this->linkFactory        = $linkFactory;
        $this->config             = $config;
        $this->coreString         = $coreString;
        $this->seoAutolinkPattern = $seoAutolinkPattern;
        $this->context            = $context;
        $this->storeManager       = $storeManager;
        $this->registry           = $registry;
    }


    /**
     * Main entry point. Inserts links into text.
     *
     * @param string $text
     *
     * @return string
     */
    public function addLinks($text)
    {
        if (isset($this->cache[$text])) {
            return $this->cache[$text];
        }

        if (strpos($this->context->getUrlBuilder()->getCurrentUrl(), '/checkout/onepage/') !== false
            || strpos($this->context->getUrlBuilder()->getCurrentUrl(), 'onestepcheckout') !== false) {
            return $text;
        }

        if ($this->checkSkipLinks() === true) {
            return $text;
        }

        $processed = Strings::replaceSpecialCharacters($text);

        $links              = $this->getLinks($processed);
        $patternsForExclude = $this->getExcludedAutoTags();
        $processed          = $this->_addLinks($processed, $links, $patternsForExclude);

        $this->cache[$text] = $processed;

        return $processed;
    }


    /**
     * @return int
     */
    protected function getStoreId()
    {
        return (!$this->storeManager->getStore()) ? 1 : $this->storeManager->getStore()->getId();
    }

    /**
     * Returns value of setting "Links limit per page"
     * @return int
     */
    public function getMaxLinkPerPage()
    {
        if ($max = $this->config->getLinksLimitPerPage($this->getStoreId())) {
            return $max;
        }

        return self::MAX_NUMBER;
    }

    /**
     * Returns collection of links with keywords which present in our text.
     * Not ALL possible links.
     * try get links with newer query, if returns SQLERROR
     * (for older Magento like 1.4 and specific MySQL configurations) -
     * get links with older query for backward compatibility
     *
     * @param string $text
     *
     * @return Link[]
     */
    public function getLinks($text)
    {
        $textArrayWithMaxSymbols = Strings::splitText($text);

        $where = [];
        foreach ($textArrayWithMaxSymbols as $splitTextVal) {
            $where[] = "lower('" . addslashes($splitTextVal) . "') LIKE CONCAT(" . "'%'" . ', lower(keyword), ' . "'%'" . ')';
        }

        $links = $this->getLinksCollection();
        $links->getSelect()->where(implode(' OR ', $where))->order('sort_order ASC');

        try {
            $links->load(); //need to load collection to catch SQLERROR if occured
        } catch (\Exception $e) {
            $links = $this->getLinksCollection();
            $links->getSelect()->where("lower(?) LIKE CONCAT('%', lower(keyword), '%')", $text)
                ->order(['LENGTH(main_table.keyword) desc']); //we need to replace long keywords firstly
        }

        return $links;
    }

    /**
     * Prepare collection acceptable for both variants of SQL queries.
     * @return \Mirasvit\SeoAutolink\Model\ResourceModel\Link\Collection|\Mirasvit\SeoAutolink\Model\Link[]
     */
    private function getLinksCollection()
    {
        $links = $this->linkFactory->create()->getCollection();
        /** @var \Mirasvit\SeoAutolink\Model\ResourceModel\Link\Collection $links */
        $links
            ->addActiveFilter()
            ->addStoreFilter($this->storeManager->getStore());

        return $links;
    }


    /**
     * Inserts links into text
     *
     * @param string   $text
     * @param array    $links
     * @param array    $excludedTags
     * @param bool|int $replacementCountForTests - max number of replaced words. used only for tests.
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function _addLinks($text, $links, $excludedTags, $replacementCountForTests = false)
    {
        if (!$links || count($links) == 0) {
            return $text;
        }

        $pregPatterns       = $this->getPatterns();
        $patternsForExclude = $this->convertTagsToPatterns($excludedTags);
        $pregPatterns       = array_merge($patternsForExclude, $pregPatterns);

        foreach ($links as $link) {
            if (strlen($link->getKeyword()) <= 1) { //one letter can't be in autolinks
                continue;
            }
            /** @var Link $link */
            $replaceKeyword = $link->getKeyword();
            $urltitle       = $link->getUrlTitle() ? "title='{$link->getUrlTitle()}' " : '';
            $nofollow       = $link->getIsNofollow() ? 'rel=\'nofollow\' ' : '';
            $target         = $link->getUrlTarget() ? "target='{$link->getUrlTarget()}' " : '';

            $replaceLimit = '';
            $limitPerPage = '';

            $html = "<a class='mst_seo_autolink autolink' href='{$this->_prepareLinkUrl($link->getUrl())}'"
                . " {$urltitle}{$target}{$nofollow}{$limitPerPage}{$replaceLimit}>"
                . $link->getKeyword() . "</a>";

            $maxReplacements = self::MAX_NUMBER;
            if ($link->getMaxReplacements() > 0) {
                $maxReplacements = $link->getMaxReplacements();
            }
            if ($replacementCountForTests) { //for tests
                $maxReplacements = $replacementCountForTests;
            }

            $direction = 0;
            switch ($link->getOccurence()) {
                case Occurence::FIRST:
                    $direction = 0;
                    break;
                case Occurence::LAST:
                    $direction = 1;
                    break;
                case Occurence::RANDOM:
                    $direction = rand(0, 1);
                    break;
            }

            $placeholder = new TextPlaceholder($text, $pregPatterns);
            $text        = $placeholder->getTokenizedText();

            $text = $this->replace($html, $text, $maxReplacements, $replaceKeyword, $direction);

            $translationTable = $placeholder->getTranslationTableArray();

            $text = $this->_restoreSourceByTranslationTable($translationTable, $text);
        }

        return $text;
    }

    /**
     * @param array $excludedTags
     * @return array
     */
    protected function convertTagsToPatterns($excludedTags)
    {
        $patternsForExclude = [];
        foreach ($excludedTags as $tag) {
            $tag                  = str_replace(' ', '', $tag);
            $patternsForExclude[] = '#' . '<' . $tag . '[^>]*>.*?</' . $tag . '>' . '#iU';
        }

        return $patternsForExclude;
    }

    /**
     * Returns link url with base url (need to get correct store code in url)
     *
     * @param string $url
     *
     * @return string
     */
    protected function _prepareLinkUrl($url)
    {
        if (strpos($url, 'http://') === false && strpos($url, 'https://') === false) {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            if (substr($url, 0, 1) == '/') {
                $url = substr($url, 1);
            }
            $url = $baseUrl . $url;
        }

        return $url;
    }

    /**
     * Returns array of patterns, which will be used to find and replace keywords
     * @return array
     */
    protected function getPatterns()
    {
        // matches for these expressions will be replaced with a unique placeholder
        $pregPatterns = [
            '#<!--.*?-->#s'       // html comments
            , '#<a [^>]*>.*?<\/a>#iU' // html links
            , '#<a(.+)((\s)+(.+))+\/a>#iU' // html links
        ];

        return $pregPatterns;
    }


    /**
     * Reconstruct the original text
     *
     * @param array  $translationTable
     * @param string $source
     *
     * @return string
     */
    protected function _restoreSourceByTranslationTable($translationTable, $source)
    {
        foreach ($translationTable as $key => $value) {
            $source = str_replace($key, $value, $source);
        }

        return $source;
    }

    /**
     * Replace words and left the same cases
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param string $replace - html which will replace the keyword
     * @param string $source - initial text
     * @param int    $maxReplacements - max number of replacements in this text.
     * @param bool|string   $replaceKeyword - keyword which will be replaced
     * @param bool|int   $direct - replace direction (from begin or from end of the text)
     *
     * @return string
     */
    protected function replace($replace, $source, $maxReplacements, $replaceKeyword = false, $direct = false)
    {
        if ($this->currentNumberOfLinks >= $this->getMaxLinkPerPage()) { //Links limit per page
            return $source;
        }

        if ($maxReplacements > 0 && $this->getRelpacementCount($replaceKeyword) > $maxReplacements) {
            return $source;
        }

        $maxReplacements -= $this->getRelpacementCount($replaceKeyword);
        $pattern         = '/' . preg_quote($replaceKeyword, '/') . '/i';
        preg_match_all(
            $pattern,
            $source,
            $replaceKeywordVariations,
            PREG_OFFSET_CAPTURE
        );

        if (isset($replaceKeywordVariations[0])) {
            $keywordVariations = $replaceKeywordVariations[0];
            if (!empty($keywordVariations)) {
                if ($direct == 1) {
                    $keywordVariations = array_slice($keywordVariations, -$maxReplacements);
                } else {
                    $keywordVariations = array_slice($keywordVariations, 0, $maxReplacements);
                }
                foreach ($keywordVariations as $keywordValue) {
                    if ($this->currentNumberOfLinks >= $this->getMaxLinkPerPage()) { //Links limit per page
                        break;
                    }

                    $replaceForVariation = preg_replace(
                        '/(\\<a.*?\\>)(.*?)(\\<\\/a\\>)/',
                        $this->prepareReplacement($keywordValue[0]),
                        $replace
                    );
                    $source              = $this->addLinksToSource(
                        $maxReplacements,
                        $direct,
                        $source,
                        $keywordValue[0],
                        $replaceForVariation
                    );
                }
                $this->_sizeExplode = 0;
            }
        }

        return $source;
    }


    /**
     * @param string $keyword
     *
     * @return string
     */
    public function prepareReplacement($keyword)
    {
        if (is_numeric(Strings::substr($keyword))) {
            $replacement = "$1 $keyword $3";
        } else {
            $replacement = '$1' . $keyword . '$3';
        }

        return $replacement;
    }

    /**
     * @param int    $maxReplacements - maximum allowed number of replacements
     * @param int    $direct - direction
     * @param string $source - initial text
     * @param string $replaceKeyword - this keyword will be replaced
     * @param string $replace -  this text will replace the keyword
     *
     * @return string
     */
    public function addLinksToSource($maxReplacements, $direct, $source, $replaceKeyword, $replace)
    {
        $originalReplaceKeyword = $replaceKeyword;
        if ($this->currentNumberOfLinks > $this->getMaxLinkPerPage()) {
            return $source;
        }

        if ($direct == 1) {
            $source         = strrev($source);
            $replaceKeyword = strrev($replaceKeyword);
            $replace        = strrev($replace);
        }
        $explodeSource        = explode($replaceKeyword, $source); // explode text
        $nextSymbol           = ['', ' ', chr(160), ',', '.', '!', '?', ')', "\n", "\r", "\r\n"]; // symbols after the word
        $prevSymbol           = [',', ' ', chr(160), '(', "\n", "\r", "\r\n"]; // symbols before the word
        $nextTextPatternArray = ['(.*?)&nbsp;$', '(.*?)&lt;span&gt;$'];    // text pattern after the word
        $prevTextPatternArray = ['^&nbsp;(.*?)', '^&lt;\/span&gt;(.*?)']; // text pattern before the word
        $nextPattern          = '/' . implode('|', $nextTextPatternArray) . '/';
        $prevPattern          = '/' . implode('|', $prevTextPatternArray) . '/';

        $sizeExplodeSource = count($explodeSource);
        $size              = 0;
        $prepareSourse     = '';

        $replaceNumberOne = false;

        $numberOfReplacements = 0;
        $isStopReplacement    = false;

        foreach ($explodeSource as $keySource => $valSource) {
            $size++;
            $replaceIsDone = false;
            if (!$isStopReplacement &&
                $size < $sizeExplodeSource &&
                $this->_sizeExplode < $maxReplacements
                && !$replaceNumberOne) {
                $lastSymbolBeforeReplacement = false;
                if (!empty($valSource[strlen($valSource) - 1])) {
                    $lastSymbolBeforeReplacement = $valSource[strlen($valSource) - 1];
                }

                $nextSymbolAfterReplacement = false;
                if (!empty($explodeSource[$keySource + 1][0])) {
                    $nextSymbolAfterReplacement = $explodeSource[$keySource + 1][0];
                }

                if ($direct == 0) {
                    $isBeforeReplacementAllowed
                        = $lastSymbolBeforeReplacement === false
                        || $lastSymbolBeforeReplacement === " "
                        || in_array($lastSymbolBeforeReplacement, $prevSymbol)
                        || preg_match($nextPattern, $valSource);

                    $isAfterReplacementAllowed
                        = $nextSymbolAfterReplacement === false
                        || $nextSymbolAfterReplacement === " "
                        || in_array($nextSymbolAfterReplacement, $nextSymbol)
                        || preg_match($nextPattern, $valSource);

                    // maxReplacements for written letters
                    if ($isBeforeReplacementAllowed && $isAfterReplacementAllowed) {
                        $prepareSourse .= $valSource . $replace;
                        $replaceIsDone = true;
                    }
                } else {
                    $isBeforeReplacementAllowed
                        = $lastSymbolBeforeReplacement === false
                        || $lastSymbolBeforeReplacement === " "
                        || in_array($lastSymbolBeforeReplacement, $nextSymbol)
                        || preg_match($prevPattern, $valSource);

                    $isAfterReplacementAllowed
                        = $nextSymbolAfterReplacement === false
                        || $nextSymbolAfterReplacement === " "
                        || in_array($nextSymbolAfterReplacement, $prevSymbol)
                        || preg_match($nextPattern, $valSource);

                    if ($isBeforeReplacementAllowed && $isAfterReplacementAllowed) {
                        $prepareSourse .= $valSource . $replace;
                        $replaceIsDone = true;
                    }
                }
                if ($replaceIsDone) {
                    $this->_sizeExplode++;
                    $replaceNumberOne = true;
                    $numberOfReplacements++;
                }
            }

            if (!$replaceIsDone) {
                if ($size < $sizeExplodeSource) {
                    $prepareSourse .= $valSource . $replaceKeyword;
                } else {
                    $prepareSourse .= $valSource;
                }
            }

            if ($this->currentNumberOfLinks + $numberOfReplacements == $this->getMaxLinkPerPage()) {
                $isStopReplacement = true;
            }
        }

        //to use maxReplacements  the desired number of times
        $this->addReplacementCount($originalReplaceKeyword, $numberOfReplacements);
        $this->currentNumberOfLinks = $this->currentNumberOfLinks + $numberOfReplacements;

        if ($direct == 1) {
            $prepareSourse = strrev($prepareSourse);
        }

        return $prepareSourse;
    }

    /**
     * Get number of already done replacements for word on the page globally
     *
     * @param string $keyword
     *
     * @return int
     */
    protected function getRelpacementCount($keyword)
    {
        if (!isset($this->_replacementsCountGlobal[strtolower($keyword)])) {
            $this->_replacementsCountGlobal[strtolower($keyword)] = 0;
        }

        return $this->_replacementsCountGlobal[strtolower($keyword)];
    }

    /**
     * Increase number of already done replacements for word on the page globally
     *
     * @param string $keyword
     * @param int    $cnt
     *
     * @return void
     */
    protected function addReplacementCount($keyword, $cnt)
    {
        if (!isset($this->_replacementsCountGlobal[strtolower($keyword)])) {
            $this->_replacementsCountGlobal[strtolower($keyword)] = 0;
        }
        $this->_replacementsCountGlobal[strtolower($keyword)] += $cnt;
    }


    /**
     * @return bool
     */
    public function checkSkipLinks()
    {
        if ($this->_isSkipLinks === false) {
            return false;
        }
        if (!$skipLinks = $this->registry->registry('skip_auto_links')) {
            $skipLinks = $this->config->getSkipLinks($this->storeManager->getStore()->getStoreId());
            if ($skipLinks) {
                $this->registry->register('skip_auto_links', $skipLinks);
            } else {
                $this->_isSkipLinks = false;
            }
        }
        if ($this->seoAutolinkPattern->checkArrayPattern(
            parse_url($this->context->getUrlBuilder()->getCurrentUrl(), PHP_URL_PATH),
            $skipLinks
        )
        ) {
            $this->_isSkipLinks = true;

            return true;
        }

        $this->_isSkipLinks = false;

        return false;
    }

    /**
     * @return array|bool
     */
    public function getExcludedAutoTags()
    {
        if (!$this->registry->registry('excluded_auto_links_tags') && $this->_isExcludedTags) {
            $excludedTags = $this->config->getExcludedTags($this->getStoreId());
            if ($excludedTags) {
                $this->registry->register('excluded_auto_links_tags', $excludedTags);
            } else {
                $this->_isExcludedTags = false;
            }
        } elseif ($this->_isExcludedTags) {
            $excludedTags = $this->registry->registry('excluded_auto_links_tags');
        }

        if (isset($excludedTags)) {
            return $excludedTags;
        }

        return [];
    }
}
