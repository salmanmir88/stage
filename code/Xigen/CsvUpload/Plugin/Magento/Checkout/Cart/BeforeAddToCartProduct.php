<?php

declare(strict_types=1);

namespace Xigen\CsvUpload\Plugin\Magento\Checkout\Cart;

use Laminas\Validator\NotEmpty;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session\Proxy as SessionProxy;
use Magento\Framework\Message\ManagerInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

class BeforeAddToCartProduct
{
    private $messageManager;
    private $cartSession;
    private $configurableProduct;
    private $url;
    private $session;
    protected $productFactory;
    protected $productRepositoryInterface;

    public function __construct(
        Configurable $configurableProduct,
        ManagerInterface $messageManager,
        SessionProxy $cartSession,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepositoryInterface,
        UrlInterface $url,
        Session $session
    ) {
        $this->messageManager = $messageManager;
        $this->cartSession = $cartSession;
        $this->configurableProduct = $configurableProduct;
        $this->productFactory = $productFactory;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->url = $url;
        $this->session = $session;
    }

    public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null)
    {
        $enableProductCartControl = true;
        $product = null;

        if ($productInfo instanceof Product) {
            $product = $productInfo;
            if (!$product->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("This product wasn't found. Verify the product and try again.")
                );
            }
        }

        if ($product) {
            if ($product->getTypeId() === 'configurable' && isset($requestInfo['super_attribute'])) {
                $childProduct = $this->configurableProduct->getProductByAttributes($requestInfo['super_attribute'], $product);
                $product = $childProduct;
            }
            $specialPriceQuantity = $product->getSpecialPriceQuantity();
            if ($product && $enableProductCartControl && $specialPriceQuantity) {
                $requestedQty = $this->_getProductRequest($requestInfo)->getQty();
                $quote = $this->cartSession->getQuote();
                if ($requestedQty > $specialPriceQuantity) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("You can only add up to " . $specialPriceQuantity . " of this product.")
                    );
                }
                if ($quote->hasProductId($product->getId())) {
                    $quoteItem = $quote->getItemByProduct($product);
                    $existingQty = $quoteItem->getQty();
                    $totalQty = $existingQty + $requestedQty;
                    if ($totalQty > $specialPriceQuantity) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __("You can only add up to " . $specialPriceQuantity . " of this product.")
                        );
                    }
                }
            }
        }

        return [$productInfo, $requestInfo];
    }
    /**
     * @param \Magento\Checkout\Model\Cart $subject
     * @param $data
     * @return array
     */
    public function beforeUpdateItems(\Magento\Checkout\Model\Cart $subject, $data)
    {
        $quote = $this->cartSession->getQuote();

        foreach ($data as $itemId => $itemInfo) {
            $quoteItem = $quote->getItemById($itemId);
            if (!$quoteItem) {
                continue;
            }

            $product = $quoteItem->getProduct();
            $sku = $product->getSku();
            $product = $this->productRepositoryInterface->get($sku, false);
            $specialPriceQuantity = $product->getSpecialPriceQuantity();
            $requestedQty = $itemInfo['qty'] ?? 0;

            if ($specialPriceQuantity && $requestedQty > $specialPriceQuantity) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("You can only order the quantity up to " . $specialPriceQuantity . " for the product " . $product->getName() . ".")
                );
            }
        }

        return [$data];
    }




    private function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof \Magento\Framework\DataObject) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new \Magento\Framework\DataObject(['qty' => $requestInfo]);
        } elseif (is_array($requestInfo)) {
            $request = new \Magento\Framework\DataObject($requestInfo);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }

        return $request;
    }
}
