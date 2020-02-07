<?php
/**
 * SpicePay PlaceOrder controller
 *
 * @category    SpicePay
 * @package     SpicePay_Merchant
 * @author      SpicePay
 * @copyright   SpicePay (https://spicepay.com)
 * @license     https://github.com/spicepay/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */

namespace SpicePay\Merchant\Controller\Payment;

use SpicePay\Merchant\Model\Payment as SpicePayPayment;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class PlaceOrder extends Action
{
    protected $orderFactory;
    protected $spicepayPayment;
    protected $checkoutSession;
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;


    /**
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param Session $checkoutSession
     * @param SpicePayPayment $spicepayPayment
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        Context $context,
        OrderFactory $orderFactory,
        Session $checkoutSession,
        SpicePayPayment $spicepayPayment,
        ScopeConfigInterface $scopeConfig
    ) {

        parent::__construct($context);
        $this->quoteRepository = $quoteRepository;
        $this->_eventManager = $eventManager;
        $this->orderFactory = $orderFactory;
        $this->spicepayPayment = $spicepayPayment;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }


    public function execute()
    {
        $order = $this->orderFactory->create()->load($_GET['orderId']);

        if (!$order->getIncrementId()) {
            $this->getResponse()->setBody(json_encode([
                'status' => false,
                'reason' => 'Order Not Found',
            ]));
            return;
        }

        ///Restores Cart
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $quote->setIsActive(1);
        $this->quoteRepository->save($quote);

        $this->spicepayPayment->getSpicePayRequest($order);
    }

}
