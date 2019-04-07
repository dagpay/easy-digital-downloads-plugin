# Dagpay for Easy Digital Downloads

* Contributors: [Rene Aavik](https://github.com/leetdev), [Dagpay](https://github.com/dagpay)
* Donate link: <https://dagpay.io/>
* Tags: Dagpay, edd, easy digital downloads, dagcoin, cryptocurrency, payment, payments, merchant, merchants
* Requires at least: 4.6
* Tested up to: 5.1.1
* Requires PHP: 7.1
* License: GPLv2 or later
* License URI: <https://www.gnu.org/licenses/gpl-2.0.html>

Dagpay helps you to accept lightning fast dagcoin payments directly from your eCommerce store.

## Description

Start accepting Dagpay payments for your business today and say goodbye to the slow transactions times, fraudulent chargebacks and to the enormous transaction fees.

Key features of Dagpay:
* Checkout with Dagpay and accept dagcoin payments on your Easy Digital Downloads store
* Wallet to wallet transactions - Dagpay does not have access to your dagcoins and/or your private keys. Your funds move safely directly to your provided DagWallet address
* Overview of all your dagcoin payments in the Dagpay merchant dashboard at <https://dagpay.io/>

### Setup & Configuration

After installing and activating the Dagpay plugin in your Wordpress Admin Panel, complete the setup according to the following instructions:

1. Go to **Downloads** -> **Settings** -> **Payment Gateways** -> **Dagpay** and note the status and redirect URLs. You will need those when creating your merchant environment.
2. Log in to your Dagpay account and head over to **Merchant Tools** -> **Integrations** and click **Add integration**.
3. Add your environment "Name", "Description" and choose your wallet for receiving payments.
4. Add the status URL for server-to-server communication and redirect URLs from the Dagpay settings page in WP.
5. Save the environment and copy the generated **Environment ID**, **User ID** and **Secret** credentials to the corresponding fields on the Dagpay settings page.
6. Save changes and go to **General** settings under **Payment Gateways**. Check **Dagpay** and save changes to enable Dagpay payments.

### Installation

This plugin requires Easy Digital Downloads, make sure you have EDD installed.

1. Start by signing up for a [Dagpay account](https://app.dagpay.io/sign-up/).
2. Download the latest version of the Dagpay plugin from the Wordpress directory.
3. Install the latest version of the Dagpay for Easy Digital Downloads plugin.
4. Navigate to your Wordpress Admin Panel and select **Plugins** -> **Add New** -> **Upload Plugin**. Select the downloaded plugin and click **Install Now**.

## Frequently Asked Questions

#### How do I pay a Dagpay invoice?

You can pay an invoice using your [dagcoin wallet](https://dagcoin.org/wallet/). Make sure you have enough funds to make the transaction, scan the QR code with your DagWallet application in the Dagpay payment view and confirm the payment in your DagWallet.

####  Is there a Dagpay test environment?

You can test Dagpay in the test environment <https://test.dagpay.io/> using the testnet dagcoin wallet.

####  Where can I get help for Dagpay?

If you have any issues setting up Dagpay for your business, you can contact us at <support@dagpay.io>

Please note that when contacting Dagpay support, describe your issue in detail and attach screenshots if necessary for troubleshooting.
