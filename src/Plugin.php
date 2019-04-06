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

    public function filterRegisterGatewaySettings(array $gatewaySettings): array
    {
        $settings = [
            'dagpay_settings' => [
                'id' => 'dagpay_settings',
                'name' => '<strong>' . __('Dagpay Settings', 'dagpay-edd') . '</strong>',
                'type' => 'header',
            ],
        ];
        $gatewaySettings['dagpay'] = $settings;
        return $gatewaySettings;
    }
}
