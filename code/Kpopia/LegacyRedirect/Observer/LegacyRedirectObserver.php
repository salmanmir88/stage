<?php

namespace Kpopia\LegacyRedirect\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Psr\Log\LoggerInterface;

class LegacyRedirectObserver implements ObserverInterface
{
    protected $redirect;
    protected $url;
    protected $request;
    protected $urlFinder;
    protected $response;
    protected $storeManager;
    protected $storeRepository;
    protected $logger;

    public function __construct(
        RedirectInterface $redirect,
        UrlInterface $url,
        RequestInterface $request,
        UrlFinderInterface $urlFinder,
        ResponseInterface $response,
        StoreManagerInterface $storeManager,
        StoreRepositoryInterface $storeRepository,
        LoggerInterface $logger
    ) {
        $this->redirect = $redirect;
        $this->url = $url;
        $this->request = $request;
        $this->urlFinder = $urlFinder;
        $this->response = $response;
        $this->storeManager = $storeManager;
        $this->storeRepository = $storeRepository;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $originalPath = ltrim($this->request->getRequestUri(), '/');
        $originalPath = strtok($originalPath, '?');
        $originalPath = rtrim($originalPath, '/');

        // Get the store ID from the request
        $storeId = $this->storeManager->getStore()->getId();

        $pathsToTry = [];

        // 1. Original path
        $pathsToTry[] = $originalPath;

        // 2. Remove .html
        $noHtml = preg_replace('/\.html$/', '', $originalPath);
        if ($noHtml !== $originalPath) {
            $pathsToTry[] = $noHtml;
        }

        // 3. Remove numeric ID suffix
        $noId = preg_replace('/-\d{2,6}(?=\/|$)/', '', $noHtml);
        if ($noId !== $noHtml && !in_array($noId, $pathsToTry)) {
            $pathsToTry[] = $noId;
        }

        // 4. Build all shorter paths (e.g. stripping categories progressively)
        $segments = explode('/', $noId);
        for ($i = 1; $i < count($segments); $i++) {
            $partialPath = implode('/', array_slice($segments, $i));
            if (!in_array($partialPath, $pathsToTry)) {
                $pathsToTry[] = $partialPath;
            }
        }

        // Loop through each cleaned path and check for URL rewrite
        foreach ($pathsToTry as $path) {
            $rewrites = $this->urlFinder->findAllByData([
                'request_path' => $path
            ]);

            if (empty($rewrites)) {
                continue;
            }

            $preferredRewrite = null;

            // First, look for a rewrite that matches the current store
            foreach ($rewrites as $rewrite) {
                if ($rewrite->getStoreId() == $storeId) {
                    $preferredRewrite = $rewrite;
                    break;
                }
            }

            // If not found, fallback to only rewrite if it's the only one
            if (!$preferredRewrite && count($rewrites) === 1) {
                $preferredRewrite = $rewrites[0];
            }

            // If we have a preferred rewrite, perform the redirect
            if ($preferredRewrite) {
                $rewriteStoreId = $preferredRewrite->getStoreId();
                $targetStore = $this->storeManager->getStore($rewriteStoreId);
                $baseUrl = rtrim($targetStore->getBaseUrl(), '/');
                $redirectUrl = $baseUrl . '/' . ltrim($path, '/');

                $this->logger->info("[LEGACY REDIRECT] $originalPath => $redirectUrl (resolved for store $rewriteStoreId)");
                $this->response->setRedirect($redirectUrl, 301);
                $this->response->sendResponse();
                exit;
            }

            // Optional: log ambiguous cases
            $this->logger->warning("[LEGACY REDIRECT WARNING] Multiple rewrites for '$path' but no match for current store ID $storeId.");
        }
    }
}

