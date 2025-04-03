<?php

namespace Xigen\CsvUpload\Model\Data;

use Xigen\CsvUpload\Api\Data\CsvInterface;

/**
 * XigenCsvUpload Csv class
 */
class Csv extends \Magento\Framework\Api\AbstractExtensibleObject implements CsvInterface
{

    /**
     * Get csv_id
     * @return string|null
     */
    public function getCsvId()
    {
        return $this->_get(self::CSV_ID);
    }

    /**
     * Set csv_id
     * @param string $csvId
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setCsvId($csvId)
    {
        return $this->setData(self::CSV_ID, $csvId);
    }

    /**
     * Get filename
     * @return string|null
     */
    public function getFilename()
    {
        return $this->_get(self::FILENAME);
    }

    /**
     * Set filename
     * @param string $filename
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setFilename($filename)
    {
        return $this->setData(self::FILENAME, $filename);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Xigen\CsvUpload\Api\Data\CsvExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Xigen\CsvUpload\Api\Data\CsvExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Xigen\CsvUpload\Api\Data\CsvExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get applied_at
     * @return string|null
     */
    public function getAppliedAt()
    {
        return $this->_get(self::APPLIED_AT);
    }

    /**
     * Set applied_at
     * @param string $applied_at
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setAppliedAt($appliedAt)
    {
        return $this->setData(self::APPLIED_AT, $appliedAt);
    }

    /**
     * Get processed
     * @return string|null
     */
    public function getProcessed()
    {
        return $this->_get(self::PROCESSED);
    }

    /**
     * Set processed
     * @param string $processed
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setProcessed($processed)
    {
        return $this->setData(self::PROCESSED, $processed);
    }

    /**
     * Get lockedToRemove
     * @return string|null
     */
    public function getLockedToRemove()
    {
        return $this->_get(self::LOCKEDTOREMOVE);
    }

    /**
     * Set lockedToRemove
     * @param string $lockedToRemove
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setLockedToRemove($lockedToRemove)
    {
        return $this->setData(self::LOCKEDTOREMOVE, $lockedToRemove);
    }

    /**
     * Get ruleName
     * @return string|null
     */
    public function getRulename()
    {
        return $this->_get(self::RULENAME);
    }

    /**
     * Set ruleName
     * @param string $ruleName
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setRulename($ruleName)
    {
        return $this->setData(self::RULENAME, $ruleName);
    }

    /**
     * Get locked
     * @return string|null
     */
    public function getLocked()
    {
        return $this->_get(self::LOCKED_TO_APPLY);
    }

    /**
     * Set locked
     * @param string $locked
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setLocked($locked)
    {
        return $this->setData(self::LOCKED_TO_APPLY, $locked);
    }

    /**
     * Get jobInProcess
     * @return string|null
     */
    public function getJobInProcess()
    {
        return $this->_get(self::JOBINPROCESS);
    }

    /**
     * Set jobInProcess
     * @param string $jobInProcess
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setJobInProcess($jobInProcess)
    {
        return $this->setData(self::JOBINPROCESS, $jobInProcess);
    }
}
