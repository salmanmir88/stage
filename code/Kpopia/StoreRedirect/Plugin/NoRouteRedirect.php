<?php
namespace Kpopia\StoreRedirect\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;

class NoRouteRedirect
{
    protected $storeManager;
    protected $response;
    protected $urlRewriteCollectionFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        ResponseHttp $response,
        UrlRewriteCollectionFactory $urlRewriteCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->response = $response;
        $this->urlRewriteCollectionFactory = $urlRewriteCollectionFactory;
    }

    public function aroundProcess(
        \Magento\Framework\App\Router\NoRouteHandlerInterface $subject,
        callable $proceed,
        RequestInterface $request
    ) {
        $requestPath = trim($request->getPathInfo(), '/');
        $currentStoreId = $this->storeManager->getStore()->getId();
        $stores = $this->storeManager->getStores();

        // Possible variations of the URL (with and without .html)
        $requestPathWithHtml = $requestPath . '.html';
        $requestPathWithoutHtml = preg_replace('/\.html$/', '', $requestPath);
        foreach ($stores as $store) {
            if ($store->getId() != $currentStoreId) {
                $urlRewriteCollection = $this->urlRewriteCollectionFactory->create()
                    ->addFieldToFilter('store_id', $store->getId())
                    ->addFieldToFilter(
                        'request_path',
                        ['in' => [$requestPath, $requestPathWithHtml, $requestPathWithoutHtml]]
                    );
                
                if ($urlRewriteCollection->getSize() > 0) {
                    $existingUrlRewrite = $urlRewriteCollection->getFirstItem();
                    $storeUrl = $store->getBaseUrl();
                    $this->response->setRedirect($storeUrl . $existingUrlRewrite->getRequestPath())->sendResponse();
                    exit;
                }
            }
        }
        
        return $proceed($request);
    }
}

