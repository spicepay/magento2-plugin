# Magento 2 SpicePay Plugin

Sign up for SpicePay account at <https://spicepay.com>

## Installation via Composer

You can install Magento 2 SpicePay plugin via [Composer](http://getcomposer.org/). Run the following command in your terminal:

1. Go to your Magento 2 root folder.

2. Enter following commands to install plugin:

    ```bash
    composer require spicepay/magento2-plugin
    ```
   Wait while dependencies are updated.

3. Enter following commands to enable plugin:

    ```bash
    php bin/magento module:enable SpicePay_Merchant --clear-static-content
    php bin/magento setup:upgrade
    ```

## Plugin Configuration

Enable and configure SpicePay plugin in Magento Admin under `Stores / Configuration / Sales / Payment Methods / Bitcoin and Altcoins via SpicePay`.
