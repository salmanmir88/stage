<?php
/**
 * Copyright © CustomWork All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\Data\StoreInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Data extends AbstractHelper
{
    const DYNAMIC_FIEID = 'dynamicrow/general/dynamic_field';
    
    /**
     * @var StoreManagerInterface  
     */
    protected $storeManager;

    /**
     * @var StoreInterface  
     */
    protected $storeInterface;

    /**
     * @var Json  
     */
    protected $serialize;

    /**
     * @var UserCollectionFactory
     */
    private $userCollectionFactory;


    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param StoreManagerInterface $storeManager
     * @param StoreInterface $storeInterface
     * @param Json $serialize
     * @param UserCollectionFactory $userCollectionFactory
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface $storeManager,
        StoreInterface $storeInterface,
        Json $serialize,
        UserCollectionFactory $userCollectionFactory,
        TimezoneInterface $localeDate
    ) {
        $this->storeManager = $storeManager;
        $this->storeInterface = $storeInterface;
        $this->serialize = $serialize;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->_localeDate = $localeDate;
        parent::__construct($context);
    }

    /**
     * Get store id
     * @return string
     */
    public function getStoreid()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get store local
     * @return string
     */
    public function getStoreLocal()
    {
        return $this->storeInterface->getLocaleCode();
    }

    /**
     * Get subject
     * @return array
     */
    public function getSubjects()
    {
        $subjectconfig = $this->scopeConfig->getValue(self::DYNAMIC_FIEID,ScopeInterface::SCOPE_STORE,$this->getStoreid());
        if($subjectconfig == '' || $subjectconfig == null)
            return;

        $unserializedata = $this->serialize->unserialize($subjectconfig);
        $subjectconfigarray = array();
        foreach($unserializedata as $key => $row)
        {
            $subjectconfigarray[] = [
                                     'english'=>$row['text_1'],
                                     'arabic'=>$row['text_2']
                                    ];
        }
        
        return $subjectconfigarray;
    }

    /**
     * Admin info 
     * @return array
     */
    public function getAdminUsers()
    {
        return $this->userCollectionFactory->create()->addFieldToSelect(['user_id','username']);
    }
    
    /**
     * @param string $time
     *
     * @return string
     */
    public function formatDateTime($time)
    {
        return $this->formatDate(
            $time,
            \IntlDateFormatter::MEDIUM
        ).' '.$this->formatTime(
            $time,
            \IntlDateFormatter::SHORT
        );
    }

     /**
     * Retrieve formatting date
     *
     * @param null|string|\DateTimeInterface $date
     * @param int $format
     * @param bool $showTime
     * @param null|string $timezone
     * @return string
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date ?? 'now');
        return $this->_localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    /**
     * Retrieve formatting time
     *
     * @param   \DateTime|string|null $time
     * @param   int $format
     * @param   bool $showDate
     * @return  string
     */
    public function formatTime(
        $time = null,
        $format = \IntlDateFormatter::SHORT,
        $showDate = false
    ) {
        $time = $time instanceof \DateTimeInterface ? $time : new \DateTime($time ?? 'now');
        return $this->_localeDate->formatDateTime(
            $time,
            $showDate ? $format : \IntlDateFormatter::NONE,
            $format
        );
    }
}
