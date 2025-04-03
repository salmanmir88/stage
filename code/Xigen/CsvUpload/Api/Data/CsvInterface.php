<?php

namespace Xigen\CsvUpload\Api\Data;

/**
 * Interface CsvInterface
 */
interface CsvInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const PROCESSED = 'processed';
    const CREATED_AT = 'created_at';
    const APPLIED_AT = 'applied_at';
    const FILENAME = 'filename';
    const CSV_ID = 'csv_id';
    const RULENAME = 'rulename';
    const LOCKED_TO_APPLY = 'locked';
    const LOCKEDTOREMOVE = 'locked_to_remove';
    const  JOBINPROCESS = 'job_in_process';

    /**
     * Get csv_id
     * @return string|null
     */
    public function getCsvId();

    /**
     * Set csv_id
     * @param string $csvId
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setCsvId($csvId);

    /**
     * Get filename
     * @return string|null
     */
    public function getFilename();

    /**
     * Set filename
     * @param string $filename
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setFilename($filename);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Xigen\CsvUpload\Api\Data\CsvExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Xigen\CsvUpload\Api\Data\CsvExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Xigen\CsvUpload\Api\Data\CsvExtensionInterface $extensionAttributes
    );

    /**
     * Get created_at
     * @return string|null
     */
    public function getcreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get applied_at
     * @return string|null
     */
    public function getAppliedAt();

    /**
     * Set applied_at
     * @param string $applied_at
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setAppliedAt($appliedAt);

    /**
     * Get rule_name
     * @return string|null
     */
    public function getRulename();

    /**
     * Set rule_name
     * @param string $ruleName
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setRulename($ruleName);

    /**
     * Get locked
     * @return string|null
     */
    public function getLocked();

    /**
     * Set locked
     * @param string $locked
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setLocked($locked);

    /**
     * Get lockedToRemove
     * @return string|null
     */
    public function getLockedToRemove();

    /**
     * Set lockedToRemove
     * @param string $lockedToRemove
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setLockedToRemove($lockedToRemove);
    
    /**
     * Get processed
     * @return string|null
     */
    public function getProcessed();

    /**
     * Set processed
     * @param string $processed
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setProcessed($processed);


    /**
     * Get jobInProcess
     * @return string|null
     */
    public function getJobInProcess();

    /**
     * Set jobInProcess
     * @param string $jobInProcess
     * @return \Xigen\CsvUpload\Api\Data\CsvInterface
     */
    public function setJobInProcess($jobInProcess);

}
