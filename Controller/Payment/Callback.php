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
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Message\ManagerInterface;

class Callback extends Action
{
    protected $order;
    protected $spicepayPayment;
    protected $orderRepository;
    protected $invoiceService;
    protected $invoiceSender;
    protected $transactionFactory;
    protected $messageManager;

    /**
     * @param Context $context
     * @param Order $order
     * @param Payment|SpicePayPayment $spicepayPayment
     * @internal param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Order $order,
        SpicePayPayment $spicepayPayment,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        TransactionFactory $transactionFactory,
        ManagerInterface $messageManager
    ) {

        parent::__construct($context);
        $this->order = $order;
        $this->spicepayPayment = $spicepayPayment;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->transactionFactory = $transactionFactory;
        $this->messageManager = $messageManager;
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
        $response = $this->spicepayPayment->validateSpicePayCallback($order);

        $this->getResponse()->setBody($response);

        if ($response == "OK") {
            try {
                if (!$order->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
                }
                if(!$order->canInvoice()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                            __('The order does not allow an invoice to be created.')
                        );
                }

                $invoice = $this->invoiceService->prepareInvoice($order);
                if (!$invoice) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t save the invoice right now.'));
                }
                if (!$invoice->getTotalQty()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('You can\'t create an invoice without products.')
                    );
                }
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);
                $order->addStatusHistoryComment('Automatically INVOICED', false);
                $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
                $transactionSave->save();

                // send invoice emails, If you want to stop mail disable below try/catch code
                try {
                    $this->invoiceSender->send($invoice);
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('We can\'t send the invoice email right now.'));
                }
            } catch (\Exception $e) {
                
                $this->messageManager->addError($e->getMessage());
            }
        }

        die($response);
    }
}
