<?php
/**
 * Copyright © ProductGoogleSheet All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\ProductGoogleSheet\Cron;

class ProductImport
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     * @var \Dakha\ProductGoogleSheet\Model\GoogleSheet\ProductSend
     */
    protected $productsend;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Dakha\ProductGoogleSheet\Model\GoogleSheet\ProductSend $productsend
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Dakha\ProductGoogleSheet\Model\GoogleSheet\ProductSend $productsend 
    )
    {
        $this->logger = $logger;
        $this->productsend = $productsend;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->productsend->save();
        } catch (Exception $e) {
            $this->logger->addInfo("Cronjob ProductImport is executed. ".$e->getMessage());   
        }
    }
}