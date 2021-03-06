<?php

namespace SpicePay\Merchant\Controller\Ajax;

use Ved\Mymodule\Model\NewsFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class GetOrder extends \Magento\Framework\App\Action\Action {
    protected $_order;
    protected $resultJsonFactory;
    protected $checkoutSession;
    protected $_logger;

    public function __construct(Context $context, 
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Psr\Log\LoggerInterface $logger,
        Session $checkoutSession)
    {
        $this->_logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
    }

    public function execute() {
        $result = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()) 
        {
          $order_id = $this->checkoutSession->getLastRealOrderId();

          $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
          $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($order_id);

          /*get customer details*/

          $custLastName= $order->getCustomerLastname();
          $custFirsrName= $order->getCustomerFirstname();
          $ipaddress=$order->getRemoteIp();
          $customer_email=$order->getCustomerEmail();
          $customerid=$order->getCustomerId();

          /* get Billing details */  
          $billingaddress=$order->getBillingAddress();
          $billingcity=$billingaddress->getCity();      
          $billingstreet=$billingaddress->getStreet();
          $billingpostcode=$billingaddress->getPostcode();
          $billingtelephone=$billingaddress->getTelephone();
          $billingstate_code=$billingaddress->getRegionCode();

          /* get shipping details */

          $shippingaddress=$order->getShippingAddress();        
          $shippingcity=$shippingaddress->getCity();
          $shippingstreet=$shippingaddress->getStreet();
          $shippingpostcode=$shippingaddress->getPostcode();      
          $shippingtelephone=$shippingaddress->getTelephone();
          $shippingstate_code=$shippingaddress->getRegionCode();

         /* get  total */

          $tax_amount=$order->getTaxAmount();
          $total=$order->getGrandTotal();
          $level = 'DEBUG';
          $this->_logger->log($level,'check_order_id', array('check_order_id'=>$order_id, 'check_totalPrice' => $total, 'check_custLastName'=>$custLastName, 'check_custFirsrName' => $custFirsrName)); 
            $result = $this->resultJsonFactory->create();
            $result->setData(['totalPrice' => $total, 'order_id' => $order_id, 'custLastName' => $custLastName, 'custFirsrName' => $custFirsrName]);


            return $result;
        }


        return "bad";
    }
}