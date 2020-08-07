<?php

namespace Salvopruiti\ShippingLimitedProducts\Observer;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Ui\DataProvider\Product\ProductCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Interceptor;

class ValidateCartObserver implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var Cart
     */
    protected $cart;
    /**
     * @var Magento\Quote\Model\Quote
     */
    private $quote;
    /**
     * @var ProductFactory
     */
    private $productFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ManagerInterface $messageManager
     * @param RedirectInterface $redirect
     * @param CustomerCart $cart
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        \Magento\Checkout\Model\Cart $cart,
        ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory
    ) {
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->redirect = $redirect;
        $this->cart = $cart;
        $this->productFactory = $productFactory;
    }

    /**
     * Validate Cart Before going to checkout
     * - event: controller_action_predispatch_checkout_index_index
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $isEnabled = (bool)$this->scopeConfig->getValue("sales/shipping_limited_products/enabled");
        $maxWeight = (float)$this->scopeConfig->getValue("sales/shipping_limited_products/max_weight");
        $error_message = $this->scopeConfig->getValue("sales/shipping_limited_products/error_message");

        if(!$isEnabled)
            return;

        $quote = $this->cart->getQuote();
        $controller = $observer->getControllerAction();

        $ids = $this->cart->getProductIds();

        $coll = $this->productFactory->create();

        $data = $coll
            ->addAttributeToFilter('shipping_limited', ['eq' => 1])
            ->addAttributeToSelect(['weight','shipping_limited'])
            ->addIdFilter($ids);

        $validCart = true;

        $totalWeight = 0;

        /** @var \Magento\Catalog\Model\Product\Interceptor $product */
        foreach($data as $product) {

            $item = $quote->getItemByProduct($product);
            $totalWeight += ($item->getRowWeight());

        }

        if ($totalWeight > $maxWeight) {
            $this->messageManager->addNoticeMessage(
                __($error_message)
            );
            $this->redirect->redirect($controller->getResponse(), 'checkout/cart');
        }
    }
}
