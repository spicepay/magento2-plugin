<?php

/**
 * SpicePay test API authorization controller
 *
 * @category    SpicePay
 * @package     SpicePay_Merchant
 * @author      SpicePay
 * @copyright   SpicePay (https://spicepay.com)
 * @license     https://github.com/spicepay/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */

namespace SpicePay\Merchant\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use SpicePay\SpicePay;
use \Magento\Store\Model\ScopeInterface;
use SpicePay\Merchant\Model\Payment\Interceptor;

class TestConnection extends Action
{

    protected $checkoutSession;
    protected $scopeConfig;


    public function __construct(
        Context $context,
        Session $checkoutSession,
        ScopeConfigInterface $scopeConfig
    )

    {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
    }


    public function execute()
    {
        if (!$this->scopeConfig->getValue('payment/spicepay_merchant/spicepay_site_id', ScopeInterface::SCOPE_STORE)) {
            $this->getResponse()->setBody(json_encode([
                'status' => false,
                'reason' => "No Site ID entered",
            ]));
                return;
        }
        if (!$this->scopeConfig->getValue('payment/spicepay_merchant/spicepay_callback_secret', ScopeInterface::SCOPE_STORE)) {
            $this->getResponse()->setBody(json_encode([
                'status' => false,
                'reason' => "No SpicePay Secret ID entered",
            ]));
                return;
        }


        $this->getResponse()->setBody(json_encode([
            'status' => true,
        ]));
            return;

    }


}
