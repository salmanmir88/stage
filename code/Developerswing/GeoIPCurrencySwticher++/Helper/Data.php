<?php
/**
 * Copyright © Geo IP Currency Swticher All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Developerswing\GeoIPCurrencySwticher\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
class Data extends AbstractHelper
{
    protected $scopeConfig;
    private $logger;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
    	$this->scopeConfig  = $scopeConfig;
    	$this->logger       = $logger;
        parent::__construct($context);
    }
    
    public function getModuleStatus()
    {
    	return $this->scopeConfig->getValue("geoipcurrencyswticher/option/enable",ScopeInterface::SCOPE_STORE);
    }
    public function getRealIpAddress(){
		 if ( !empty($_SERVER['HTTP_CLIENT_IP']) ) {
		  $ip = $_SERVER['HTTP_CLIENT_IP'];
		 } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
		  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		 } else {
		  $ip = $_SERVER['REMOTE_ADDR'];
		 }
		 return $ip;
	}

	public function getCountryCode()
	{
	   try{
	        $vis_ip = $this->getRealIpAddress();
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://freegeoip.app/json/".$vis_ip,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
			    "cache-control: no-cache",
			    "content-type: application/json",
			    "postman-token: c28b10bc-eff2-0472-12ab-ed6480c7352a"
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  return 'SA';
			} else {
			  $response = json_decode($response);
			  return $response->country_code;
			}
	    } catch (\Exception $e) {
	    	
	        $this->logger->critical($e->getMessage());
	    }
	}
}

