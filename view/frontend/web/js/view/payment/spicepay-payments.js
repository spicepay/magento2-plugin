/**
 * SpicePay payment method model
 *
 * @category    SpicePay
 * @package     SpicePay_Merchant
 * @author      SpicePay
 * @copyright   SpicePay (https://spicepay.com)
 * @license     https://github.com/spicepay/magento2-plugin/blob/master/LICENSE The MIT License (MIT)
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'spicepay_merchant',
                component: 'SpicePay_Merchant/js/view/payment/method-renderer/spicepay-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
