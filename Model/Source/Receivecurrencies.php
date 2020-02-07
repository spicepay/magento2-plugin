<?php
/**
 * Receive currencies Source Model
 *
 * @category    SpicePay
 * @package     SpicePay_Merchant
 * @author      SpicePay
 * @copyright   SpicePay (https://spicepay.com)
 * @license     https://github.com/spicepay/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */
namespace SpicePay\Merchant\Model\Source;

class Receivecurrencies
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
          ['value' => 'USD', 'label' => 'USD'],
          ['value' => 'EUR', 'label' => 'EUR'],
          ['value' => 'GBP', 'label' => 'GBP'],
          ['value' => 'CAD', 'label' => 'CAD']
        ];
    }
}
