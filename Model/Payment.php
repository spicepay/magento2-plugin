<?php
/**
 * SpicePay payment method model
 *
 * @category    SpicePay
 * @package     SpicePay_Merchant
 * @author      SpicePay
 * @copyright   SpicePay (https://spicepay.com)
 * @license     https://github.com/spicepay/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */
namespace SpicePay\Merchant\Model;

use SpicePay\SpicePay;
use SpicePay\Merchant as SpicePayMerchant;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\OrderManagementInterface;

class Payment extends AbstractMethod
{
    const SPICEPAY_MAGENTO_VERSION = '1.0.6';
    const CODE = 'spicepay_merchant';

    protected $_code = 'spicepay_merchant';

    protected $_isInitializeNeeded = true;

    protected $urlBuilder;
    protected $spicepay;
    protected $storeManager;
    protected $orderManagement;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param SpicePayMerchant $spicepay
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param OrderManagementInterface $orderManagement
     * @param array $data
     * @internal param ModuleListInterface $moduleList
     * @internal param TimezoneInterface $localeDate
     * @internal param CountryFactory $countryFactory
     * @internal param Http $response
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        OrderManagementInterface $orderManagement,
        SpicePayMerchant $spicepay,
        array $data = [],
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null

    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->orderManagement = $orderManagement;
        $this->spicepay = $spicepay;

    }

    /**
     * @param Order $order
     * @return array
     */
    public function getSpicePayRequest($order)
    {
        $update_order = $this->validateSpicePayCallback($order);
    }

    /**
     * @param Order $order
     */
    public function validateSpicePayCallback($order)
    {
        try {
            if (isset($_POST['paymentId']) && isset($_POST['orderId']) && isset($_POST['hash']) 
            && isset($_POST['paymentCryptoAmount']) && isset($_POST['paymentAmountUSD']) 
            && isset($_POST['receivedCryptoAmount']) && isset($_POST['receivedAmountUSD'])) {
                
                $paymentId = addslashes(filter_input(INPUT_POST, 'paymentId', FILTER_SANITIZE_STRING));
                $orderId = addslashes(filter_input(INPUT_POST, 'orderId', FILTER_SANITIZE_STRING));
                $hash = addslashes(filter_input(INPUT_POST, 'hash', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));    
                $clientId = addslashes(filter_input(INPUT_POST, 'clientId', FILTER_SANITIZE_STRING));
                $paymentAmountBTC = addslashes(filter_input(INPUT_POST, 'paymentAmountBTC', FILTER_SANITIZE_NUMBER_INT));
                $paymentAmountUSD = addslashes(filter_input(INPUT_POST, 'paymentAmountUSD', FILTER_SANITIZE_STRING));
                $receivedAmountBTC = addslashes(filter_input(INPUT_POST, 'receivedAmountBTC', FILTER_SANITIZE_NUMBER_INT));
                $receivedAmountUSD = addslashes(filter_input(INPUT_POST, 'receivedAmountUSD', FILTER_SANITIZE_STRING));
                $status = addslashes(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));
                $secretCode = $this->getConfigData('spicepay_site_id');
                if(isset($_POST['paymentCryptoAmount']) && isset($_POST['receivedCryptoAmount'])) {
                    $paymentCryptoAmount = addslashes(filter_input(INPUT_POST, 'paymentCryptoAmount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
                    $receivedCryptoAmount = addslashes(filter_input(INPUT_POST, 'receivedCryptoAmount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
                }
                else {
                    $paymentCryptoAmount = addslashes(filter_input(INPUT_POST, 'paymentAmountBTC', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
                    $receivedCryptoAmount = addslashes(filter_input(INPUT_POST, 'receivedAmountBTC', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
                }

                $magentoOrderId = $orderId;
                $zeroes =  9 - (int) strlen($magentoOrderId);
                for ($i=0; $i < $zeroes; $i++) { 
                    $magentoOrderId= '0'.$magentoOrderId;
                }

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $orders = $objectManager->create('Magento\Sales\Model\Order')->load($magentoOrderId);
                $orderTotal= $orders->getGrandTotal();
                $hashString = $secretCode . $paymentId . $orderId . $clientId . $paymentCryptoAmount . $paymentAmountUSD . $receivedCryptoAmount . $receivedAmountUSD . $status;

                if (!empty($orderTotal)) {
                    if (0 == strcmp(md5($hashString), $hash)) {
                    
                        $sum = number_format($orderTotal, 2, '.', ''); 
                          if ((float)$sum != $receivedAmountUSD) {
                                    throw new \Exception('Bad amount.');
                          } else {

                                if ($status == 'paid') {
                                    $orders->setState(Order::STATE_PROCESSING);
                                    $orders->setStatus($orders->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
                                    $orders->save();
                                } elseif (in_array($status, ['invalid', 'expired', 'canceled', 'refunded'])) {
                                    $this->orderManagement->cancel($orderId);

                                }

                          
                          }
                        
                    }
                    
                }else{
                   throw new \Exception('SpicePay Order #' . $request_id . ' does not exist');
                }
                
                
            } else {
                throw new \Exception('Fail');
            }

        } catch (\Exception $e) {
            $this->_logger->error($e);
        }
    }
}
