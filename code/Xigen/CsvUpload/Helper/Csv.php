<?php

namespace Xigen\CsvUpload\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;

/**
 * Csv helper class
 */
class Csv extends AbstractHelper
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\File\Csv
     */
    private $csvProcessor;

    /**
     * @var \Xigen\CsvUpload\Model\CsvFactory
     */
    private $csvFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $fileSystemIo;

    /**
     * Csv constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Xigen\CsvUpload\Model\CsvFactory $csvFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\Filesystem\Io\File $fileSystemIo
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\File\Csv $csvProcessor,
        \Xigen\CsvUpload\Model\CsvFactory $csvFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Filesystem\Io\File $fileSystemIo
    ) {
        $this->logger = $logger;
        $this->csvProcessor = $csvProcessor;
        $this->csvFactory = $csvFactory;
        $this->directoryList = $directoryList;
        $this->dateTime = $dateTime;
        $this->serializer = $serializer;
        $this->fileSystemIo = $fileSystemIo;
        parent::__construct($context);
    }

    /**
     * Get unprocssed file collection
     * @return \Xigen\CsvUpload\Model\ResourceModel\Csv\Collection;
     */
    public function getUnprocessedFiles()
    {
        $csvCollection = $this->csvFactory->create()
            ->getCollection()
            ->addFieldToFilter('processed', ['eq' => '0']);
        return $csvCollection;
    }

    /**
     * Get unprocssed item
     * @return \Xigen\CsvUpload\Model\Csv;
     */
    public function getUnprocessedFile()
    {
        $csvCollection = $this->getUnprocessedFiles();
        if ($csvCollection->getSize()) {
            return $csvCollection->getFirstItem();
        }
        return null;
    }

    /**
     * Load file by Id
     * @param int $fileId
     * \Xigen\CsvUpload\Model\Csv
     */
    public function loadFileById($fileId)
    {
        return $this->csvFactory->create()->load($fileId);
    }

    /**
     * Get clean filename
     * @param string $string
     * @return string
     */
    public function getFilename($url = null)
    {
        if (!$url) {
            throw new LocalizedException('Url "' . $url . '" is blank');
        }
        $removePrefix = explode('//', $url);
        $parts = explode('/', $removePrefix[1]);
        krsort($parts);
        $parts = array_values($parts);
        $fileInfo = $this->fileSystemIo->getPathInfo($url);
        return '/' . $parts[1] . '/' . $fileInfo['basename'];
    }

    /**
     * Get file path
     * @param string $string
     * @return string
     */
    public function getFilepath()
    {
        return $this->directoryList->getPath('media') . '/csv';
    }

    /**
     * Update process flag
     * @param int $csvId
     * @param int $processId
     * @return void
     */
    public function setCsvFileProcessToId($csvId = null, $processId = null)
    {
        if (!$csvId) {
            throw new LocalizedException(__("Problem setting file ID $csvId as Status ID $processId"));
        }

        $fileToProcess = $this->csvFactory->create()->load($csvId);
        $fileToProcess->setProcessed($processId);
        $fileToProcess->setAppliedAt($this->dateTime->gmtDate());
        try {
            $fileToProcess->save();
            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return false;
    }

    // setAppliedAt($fileToProcess->getId()); 

    // /**
    //  * Update process flag
    //  * @param int $csvId
    //  * @param int $processId
    //  * @return void
    //  */
    // public function setAppliedAt($csvId = null)
    // {
    //     if (!$csvId) {
    //         throw new LocalizedException(__("Problem setting file ID $csvId"));
    //     }

    //     $fileToProcess = $this->csvFactory->create()->load($csvId);
    //     $fileToProcess->setAppliedAt($this->dateTime->gmtDate());
    //     try {
    //         $fileToProcess->save();
    //         return true;
    //     } catch (\Exception $e) {
    //         $this->logger->critical($e);
    //     }
    //     return false;
    // }

    /**
     * Update process flag
     * @param int $csvId
     * @param int $processId
     * @return void
     */
    public function setCsvFileLockedToApplyToId($csvId = null, $processId = null)
    {
        if (!$csvId) {
            throw new LocalizedException(__("Problem setting file ID $csvId "));
        }
        $fileToProcess = $this->csvFactory->create()->load($csvId);
        $fileToProcess->setLocked($processId);
        try {
            $fileToProcess->save();
            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return false;
    }

    /**
     * Update process flag
     * @param int $csvId
     * @param int $processId
     * @return void
     */
    public function setRemoveFileLockedToId($csvId = null, $processId = null)
    {
        if (!$csvId) {
            throw new LocalizedException(__("Problem setting file ID $csvId "));
        }

        $fileToProcess = $this->csvFactory->create()->load($csvId);
        $fileToProcess->setLockedToRemove($processId);
        try {
            $fileToProcess->save();
            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return false;
    }

    /**
     * Update jobInProcess flag
     * @param int $csvId
     * @param int $processId
     * @return void
     */
    public function setJobInProcessToId($csvId = null, $processId = null)
    {
        if (!$csvId) {
            throw new LocalizedException(__("Problem setting file ID $csvId "));
        }

        $fileToProcess = $this->csvFactory->create()->load($csvId);
        $fileToProcess->setJobInProcess($processId);
        try {
            $fileToProcess->save();
            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return false;
    }



    /**
     * Check if any rows have the locked value set to 1
     * @return bool
     */
    public function isAnyCsvFileLockedToRemove()
    {
        $collection = $this->csvFactory->create()->getCollection();
        $collection->addFieldToFilter('locked_to_remove', 1);
        return $collection->getSize() > 0;
    }
    /**
     * Check if any rows have the locked value set to 1
     * @return bool
     */
    public function isAnyCsvFileLockedToApply()
    {
        $collection = $this->csvFactory->create()->getCollection();
        $collection->addFieldToFilter('locked', 1);
        return $collection->getSize() > 0;
    }

    /**
     * Check if any rows have the locked value set to 1
     * @return bool
     */
    public function isAnyCsvFileApplied()
    {
        $collection = $this->csvFactory->create()->getCollection();
        $collection->addFieldToFilter('processed', 1);
        return $collection->getSize() > 0;
    }

    /**
     * Check if any rows have the job in process value set to 1
     * @return bool
     */
    public function isAnyJobInProcess()
    {
        $collection = $this->csvFactory->create()->getCollection();
        $collection->addFieldToFilter('job_in_process', 1);
        return $collection->getSize() > 0;
    }

    /**
     * Check if any rows have the locked value set to 1
     * @return int|null
     */
    public function getLockedToApplyCsvFileId()
    {
        $collection = $this->csvFactory->create()->getCollection();
        $collection->addFieldToFilter('locked', 1);

        // Return the first ID or null if not found
        return $collection->getSize() > 0 ? $collection->getFirstItem()->getData('csv_id') : null;
    }

    /**
     * Check if any rows have the locked value set to 1
     * @return int|null
     */
    public function getLockedToRemoveCsvFileId()
    {
        $collection = $this->csvFactory->create()->getCollection();
        $collection->addFieldToFilter('locked_to_remove', 1);

        // Return the first ID or null if not found
        return $collection->getSize() > 0 ? $collection->getFirstItem()->getData('csv_id') : null;
    }

    /**
     * Check if any rows have the locked value set to 1
     * @return int|null
     */
    public function getLockedToApplyCsvFileurl()
    {
        $collection = $this->csvFactory->create()->getCollection();
        $collection->addFieldToFilter('locked', 1);

        // Return the first ID or null if not found
        return $collection->getSize() > 0 ? $collection->getFirstItem()->getData('filename') : null;
    }

    /**
     * Check if any rows have the processed value set to 1
     * @return int|null
     */
    public function getAppliedCsvFileId()
    {
        $collection = $this->csvFactory->create()->getCollection();
        $collection->addFieldToFilter('processed', 1);

        // Return the first ID or null if not found
        return $collection->getSize() > 0 ? $collection->getFirstItem()->getData('csv_id') : null;
    }

    /**
     * Reset locked value to 0 for all records with status 1
     * @return void
     */
    public function resetValueForAppliedFiles()
    {
        $collection = $this->csvFactory->create()->getCollection();
        $collection->addFieldToFilter('processed', 1);
        foreach ($collection as $file) {
            $file->setProcessed(0);
            $file->setAppliedAt(null);
            $file->save();
        }
    }

    /**
     * Define top level array
     * @return array
     */
    public function getTopLevelArray()
    {
        return ['sku', 'description', 'short_description'];
    }

    /**
     * Define top level ignore array
     * @return array
     */
    public function getTopLevelIgnoreArray()
    {
        return ['import_id', 'created_at', 'updated_at'];
    }
}
