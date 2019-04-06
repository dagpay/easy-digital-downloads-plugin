<?php

namespace Ultraleet\DagpayEDD;

final class Plugin
{
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
        $setupInfoText = __('When setting up your merchant integration environment, use the following URLs:',
                'dagpay-edd') . '<br /><p>' .
            __('Status URL', 'dagpay-edd') . ': <code>' . $this->getStatusURI() . '</code><br />' .
            __('Browser redirect (SUCCESS)',
                'dagpay-edd') . ': <code>' . $this->getRedirectSuccessURI() . '</code><br />' .
            __('Browser redirect (CANCEL and FAIL)',
                'dagpay-edd') . ': <code>' . $this->getRedirectFailURI() . '</code></p>';
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
        return add_query_arg([
            'payment-confirmation' => 'dagpay',
        ], get_permalink(edd_get_option('success_page', false)));
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
}
