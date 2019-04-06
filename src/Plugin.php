<?php

namespace Ultraleet\DagpayEDD;

use DagpayClient;
use EDD_Payment;
use Exception;
use stdClass;

final class Plugin
{
    private $client;
    private $settings;

    public function __construct()
    {
        $this->registerHooks();
    }

    /**
     * Register plugin actions and filters.
     */
    private function registerHooks()
    {
        // load plugin text domain
        add_action('plugins_loaded', [$this, 'actionLoadTextDomain']);

        // easy digital downloads hooks
        add_filter('edd_payment_gateways', [$this, 'filterRegisterGateway']);
        add_action('edd_dagpay_cc_form', '__return_false');
        add_filter('edd_settings_sections_gateways', [$this, 'filterRegisterGatewaySettingsSection']);
        add_filter('edd_settings_gateways', [$this, 'filterRegisterGatewaySettings']);
        add_action('edd_gateway_dagpay', [$this, 'actionProcessPurchase']);
        add_action('init', [$this, 'actionVerifyPayment']);
        add_filter('edd_payment_confirm_dagpay', [$this, 'filterConfirmPageContent']);
    }

    /**
     * Load plugin text domain.
     */
    public function actionLoadTextDomain()
    {
        load_plugin_textdomain(
            'dagpay-edd',
            false,
            ULTRALEET_DAGPAY_EDD_LANGUAGES_PATH
        );
    }

    /**
     * Register payment gateway.
     *
     * @param array $gateways
     * @return array
     */
    public function filterRegisterGateway(array $gateways): array
    {
        $label = __('Dagpay', 'dagpay-edd');
        $gateways['dagpay'] = ['admin_label' => $label, 'checkout_label' => $label];
        return $gateways;
    }

    /**
     * Add section to payment gateway settings.
     *
     * @param array $gatewaySections
     * @return array
     */
    public function filterRegisterGatewaySettingsSection(array $gatewaySections): array
    {
        $gatewaySections['dagpay'] = __('Dagpay', 'dagpay-edd');
        return $gatewaySections;
    }

    /**
     * @param array $gatewaySettings
     * @return array
     */
    public function filterRegisterGatewaySettings(array $gatewaySettings): array
    {
        $setupInfoText = __(
                'When setting up your merchant integration environment, use the following URLs:',
                'dagpay-edd'
            ) . '<br /><p>' .
            __('Status URL', 'dagpay-edd') . ': <code>' . $this->getStatusURI() . '</code><br />' .
            __(
                'Browser redirect (SUCCESS)',
                'dagpay-edd'
            ) . ': <code>' . $this->getRedirectSuccessURI() . '</code><br />' .
            __(
                'Browser redirect (CANCEL and FAIL)',
                'dagpay-edd'
            ) . ': <code>' . $this->getRedirectFailURI() . '</code></p>';
        $settings = [
            'dagpay_settings' => [
                'id' => 'dagpay_settings',
                'name' => '<strong>' . __('Dagpay Settings', 'dagpay-edd') . '</strong>',
                'type' => 'header',
            ],
            'dagpay_urls_config_desc' => [
                'id' => 'dagpay_urls_config_desc',
                'name' => __('Status & redirect URLs', 'dagpay-edd'),
                'type' => 'descriptive_text',
                'desc' => $setupInfoText,
            ],
            'dagpay_live_env_id' => [
                'id' => 'dagpay_live_env_id',
                'name' => __('Live Environment ID', 'dagpay-edd'),
                'desc' => __('Your Dagpay live environment unique identifier. ', 'dagpay-edd'),
                'type' => 'text',
                'size' => 'regular',
            ],
            'dagpay_live_user_id' => [
                'id' => 'dagpay_live_user_id',
                'name' => __('Live User ID', 'dagpay-edd'),
                'desc' => __('Your Dagpay live environment user identifier. ', 'dagpay-edd'),
                'type' => 'text',
                'size' => 'regular',
            ],
            'dagpay_live_secret' => [
                'id' => 'dagpay_live_secret',
                'name' => __('Live Secret', 'dagpay-edd'),
                'desc' => __('Your Dagpay live environment secret. ', 'dagpay-edd'),
                'type' => 'text',
                'size' => 'regular',
            ],
            'dagpay_test_env_id' => [
                'id' => 'dagpay_test_env_id',
                'name' => __('Test Environment ID', 'dagpay-edd'),
                'desc' => __('Your Dagpay test environment unique identifier. ', 'dagpay-edd'),
                'type' => 'text',
                'size' => 'regular',
            ],
            'dagpay_test_user_id' => [
                'id' => 'dagpay_test_user_id',
                'name' => __('Test User ID', 'dagpay-edd'),
                'desc' => __('Your Dagpay test environment user identifier. ', 'dagpay-edd'),
                'type' => 'text',
                'size' => 'regular',
            ],
            'dagpay_test_secret' => [
                'id' => 'dagpay_test_secret',
                'name' => __('Test Secret', 'dagpay-edd'),
                'desc' => __('Your Dagpay test environment secret. ', 'dagpay-edd'),
                'type' => 'text',
                'size' => 'regular',
            ],
        ];
        $gatewaySettings['dagpay'] = $settings;
        return $gatewaySettings;
    }

    /**
     * Generate endpoint URI for receiving invoice status updates.
     *
     * @return string
     */
    private function getStatusURI(): string
    {
        return add_query_arg('edd-listener', 'dagpay', home_url());
    }

    /**
     * Generate URI to redirect user after successful payment.
     *
     * @return string
     */
    private function getRedirectSuccessURI(): string
    {
        return add_query_arg(
            [
                'payment-confirmation' => 'dagpay',
            ],
            get_permalink(edd_get_option('success_page', false))
        );
    }

    /**
     * Generate URI to redirect user after failed payment.
     *
     * @return string
     */
    private function getRedirectFailURI(): string
    {
        return edd_get_failed_transaction_uri();
    }

    /**
     * @param $purchaseData
     */
    public function actionProcessPurchase($purchaseData)
    {
        if (!wp_verify_nonce($purchaseData['gateway_nonce'], 'edd-gateway')) {
            wp_die(
                __('Nonce verification has failed', 'easy-digital-downloads'),
                __('Error', 'easy-digital-downloads'),
                ['response' => 403]
            );
        }
        $paymentData = [
            'price' => $purchaseData['price'],
            'date' => $purchaseData['date'],
            'user_email' => $purchaseData['user_email'],
            'purchase_key' => $purchaseData['purchase_key'],
            'currency' => edd_get_currency(),
            'downloads' => $purchaseData['downloads'],
            'user_info' => $purchaseData['user_info'],
            'cart_details' => $purchaseData['cart_details'],
            'gateway' => 'dagpay',
            'status' => !empty($purchaseData['buy_now']) ? 'private' : 'pending',
        ];
        $paymentId = edd_insert_payment($paymentData);
        if (!$paymentId) {
            edd_record_gateway_error(
                __('Payment Error', 'easy-digital-downloads'),
                sprintf(
                    __('Payment creation failed before sending buyer to DagPay. Payment data: %s', 'dagpay-edd'),
                    json_encode($paymentData)
                ),
                $paymentId
            );
            edd_send_back_to_checkout('?payment-mode=' . $purchaseData['post_data']['edd-gateway']);
        } else {
            $client = $this->getClient();
            $invoice = null;
            try {
                $invoiceId = $this->getInvoiceId($paymentId);
                if ($invoiceId) {
                    $invoice = $client->getInvoiceInfo($invoiceId);
                }
                if (!$invoice || !$this->isInvoiceUnpaid($invoice)) {
                    $invoice = $this->createInvoice($paymentId, $purchaseData['price']);
                    wp_redirect($invoice->paymentUrl);
                    exit;
                }
            } catch (Exception $e) {
                edd_record_gateway_error(
                    __('Payment Error', 'dagpay-edd'),
                    sprintf(
                        __('Unable to create Dagpay invoice: %s', 'easy-digital-downloads'),
                        json_encode($e->getMessage())
                    )
                );
                edd_debug_log('Exception trying to create DagPay invoice: ' . $e);
            }
        }
    }

    /**
     * Get Dagcoin invoice ID associated with payment.
     *
     * @param int $paymentId
     * @return mixed
     */
    private function getInvoiceId(int $paymentId)
    {
        return get_post_meta($paymentId, '_dagcoin_invoice_id', true);
    }

    /**
     * Check invoice paid status.
     *
     * @param stdClass $invoice
     * @return bool
     */
    private function isInvoiceUnpaid(stdClass $invoice): bool
    {
        return !in_array($invoice->state, ['EXPIRED', 'CANCELLED', 'FAILED']);
    }

    /**
     * Create new invoice via Dagpay API.
     *
     * @param int $paymentId
     * @param $total
     * @return array|mixed|object
     */
    public function createInvoice(int $paymentId, $total)
    {
        $client = $this->getClient();
        $invoice = $client->createInvoice($paymentId, edd_get_currency(), $total);
        $this->setInvoiceId($paymentId, $invoice->id);
        $note = sprintf(__('Dagcoin Invoice ID: %s', 'dagpay-edd'), $invoice->id);
        edd_insert_payment_note($paymentId, $note);
        return $invoice;
    }

    /**
     * Associate Dagcoin invoice ID with payment.
     *
     * @param int $paymentId
     * @param int $invoiceId
     */
    private function setInvoiceId(int $paymentId, int $invoiceId)
    {
        update_post_meta($paymentId, '_dagcoin_invoice_id', $invoiceId);
    }

    /**
     * Listen for Dagpay status updates and verify payments.
     */
    public function actionVerifyPayment()
    {
        if (isset($_GET['edd-listener']) && $_GET['edd-listener'] == 'dagpay') {
            edd_debug_log('DagPay IPN endpoint loaded');
            $data = json_decode(file_get_contents('php://input'));
            $client = $this->getClient();
            $signature = $client->getInvoiceInfoSignature($data);
            if ($signature != $data->signature) {
                exit;
            }
            $paymentId = (int)$data->paymentId;
            $payment = new EDD_Payment($paymentId);
            switch ($data->state) {
                case 'PAID':
                case 'PAID_EXPIRED':
                    edd_insert_payment_note($paymentId, __('Dagcoin Invoice has been paid', 'dagpay-edd'));
                    edd_set_payment_transaction_id($paymentId, $data->id);
                    edd_update_payment_status($paymentId, 'publish');
                    break;
                case 'CANCELLED':
                    if (get_post_meta($paymentId, '_dagcoin_invoice_id_cancelled', true) === $this->getInvoiceId(
                            $paymentId
                        )) {
                        delete_post_meta($paymentId, '_dagcoin_invoice_id_cancelled');
                    } else {
                        $payment->update_status('revoked');
                        $payment->save();
                    }
                    edd_insert_payment_note($payment, __('Dagcoin Invoice has been cancelled', 'dagpay-edd'));
                    break;
                case 'EXPIRED':
                    $payment->update_status('failed');
                    $payment->save();
                    edd_insert_payment_note($paymentId, __('Dagcoin Invoice has expired', 'dagpay-edd'));
                    break;
                case 'FAILED':
                    $payment->update_status('failed');
                    $payment->save();
                    edd_insert_payment_note($paymentId, __('Dagcoin Invoice has failed', 'dagpay-edd'));
                    break;
            }
            exit;
        }
    }

    /**
     * Shows "Purchase Processing" message for payments that are still pending on site return.
     *
     * @param string $content
     * @return false|string
     */
    public function filterConfirmPageContent(string $content)
    {
        if (!isset($_GET['payment-id']) && !edd_get_purchase_session()) {
            return $content;
        }
        edd_empty_cart();
        $paymentId = isset($_GET['payment-id']) ? absint($_GET['payment-id']) : false;
        if (!$paymentId) {
            $session = edd_get_purchase_session();
            $paymentId = edd_get_purchase_id_by_key($session['purchase_key']);
        }
        $payment = new EDD_Payment($paymentId);
        if ($payment->ID > 0 && 'pending' == $payment->status) {
            ob_start();
            edd_get_template_part('payment', 'processing');
            $content = ob_get_clean();
        }
        return $content;
    }

    /**
     * Initialize Dagpay client if necessary and return it.
     *
     * @return DagpayClient
     */
    private function getClient(): DagpayClient
    {
        if (!isset($this->client)) {
            $settings = $this->getSettings();
            $this->client = new DagpayClient(
                $settings['environmentId'],
                $settings['userId'],
                $settings['secret'],
                edd_is_test_mode(),
                'wordpress'
            );
        }
        return $this->client;
    }

    /**
     * Get Dagpay settings.
     *
     * @return array
     */
    private function getSettings(): array
    {
        if (!isset($this->settings)) {
            $mode = $this->getMode();
            $this->settings = [
                'environmentId' => edd_get_option("dagpay_{$mode}_env_id"),
                'userId' => edd_get_option("dagpay_{$mode}_user_id"),
                'secret' => edd_get_option("dagpay_{$mode}_secret"),
            ];
        }
        return $this->settings;
    }

    /**
     * Get EDD payment mode (test/live).
     *
     * @return string
     */
    private function getMode(): string
    {
        return edd_is_test_mode() ? 'test' : 'live';
    }
}
