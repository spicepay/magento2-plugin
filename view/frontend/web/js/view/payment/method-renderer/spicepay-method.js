/**
 * SpicePay JS
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
     'jquery',
     'Magento_Checkout/js/view/payment/default',
     'Magento_Checkout/js/action/place-order',
     'Magento_Checkout/js/action/select-payment-method',
     'Magento_Customer/js/model/customer',
     'Magento_Checkout/js/checkout-data',
     'Magento_Checkout/js/model/payment/additional-validators',
     'mage/url',
 ],
 function (
     $,
     Component,
     placeOrderAction,
     selectPaymentMethodAction,
     customer,
     checkoutData,
     additionalValidators,
     url) {
     'use strict';


     return Component.extend({
         defaults: {
             template: 'SpicePay_Merchant/payment/spicepay-form'
         },

         placeOrder: function (data, event) {

             if (event) {
                 event.preventDefault();
             }
             var self = this,
                 placeOrder,
                 emailValidationResult = customer.isLoggedIn(),
                 loginFormSelector = 'form[data-role=email-with-possible-login]';
             if (!customer.isLoggedIn()) {
                 $(loginFormSelector).validation();
                 emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
             }
             if (emailValidationResult && this.validate() && additionalValidators.validate()) {
                 this.isPlaceOrderActionAllowed(false);
                 placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                 $.when(placeOrder).fail(function () {
                     self.isPlaceOrderActionAllowed(true);
                 }).done(this.afterPlaceOrder.bind(this));
                 return true;
             }
             return false;
         },

         selectPaymentMethod: function() {

             selectPaymentMethodAction(this.getData());
             checkoutData.setSelectedPaymentMethod(this.item.method);
             return true;
         },

         afterPlaceOrder: function (quoteId) {
            var firstname = window.checkoutConfig.customerData.firstname;
            var lastname = window.checkoutConfig.customerData.lastname;
            var currency = window.checkoutConfig.quoteData.store_currency_code;
            var grand_total = window.checkoutConfig.quoteData.grand_total;
            var spicepay_site_id = window.checkoutConfig.spicepay_site_id;

            var url = 'https://www.spicepay.com/p.php';
            var form = $('<form action="' + url + '" method="post">' +
              '<input type="text" name="amount" value="'+grand_total+'" />' +
              '<input type="text" name="currency" value="'+currency+'" />' +
              '<input type="text" name="orderId" value="' + quoteId + '" />' +
              '<input type="text" name="siteId" value="'+spicepay_site_id+'" />' +
              '<input type="text" name="clientName" value="'+firstname+' '+ lastname+'" />' +
              '<input type="text" name="language" value="en" />' +
              '</form>');
            $('body').append(form);
            // form.submit();

         }
     });
   }
);
