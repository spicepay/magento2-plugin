<?php
/**
 * SpicePay Callback controller
 *
 * @category    SpicePay
 * @package     SpicePay_Merchant
 * @author      SpicePay
 * @copyright   SpicePay (https://spicepay.com)
 * @license     https://github.com/spicepay/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */
namespace SpicePay\Merchant\Controller\Payment;

use SpicePay\Merchant\Model\Payment as SpicePayPayment;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;

class Callback extends Action
{
    protected $order;
    protected $spicepayPayment;

    /**
     * @param Context $context
     * @param Order $order
     * @param Payment|SpicePayPayment $spicepayPayment
     * @internal param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Order $order,
        SpicePayPayment $spicepayPayment
    ) {

        parent::__construct($context);
        $this->order = $order;
        $this->spicepayPayment = $spicepayPayment;

        $this->execute();
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $request_order_id = (filter_input(INPUT_POST, 'order_id')
            ? filter_input(INPUT_POST, 'order_id') : filter_input(INPUT_GET, 'order_id'));

        $order = $this->order->loadByIncrementId($request_order_id);
        $this->spicepayPayment->validateSpicePayCallback($order);

        $this->getResponse()->setBody('OK');
    }
}
