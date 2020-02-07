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
use Magento\Checkout\Model\ConfigProviderInterface;

class AdditionalConfigVars extends AbstractMethod
{
    const SPICEPAY_MAGENTO_VERSION = '1.0.7';
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
	public function getConfig()
	{
		$additionalVariables['spicepay_site_id'] = $this->getConfigData('spicepay_site_id');
		return $additionalVariables;
	}
}