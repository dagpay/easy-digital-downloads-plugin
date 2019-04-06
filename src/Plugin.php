<?php

namespace Ultraleet\DagpayEDD;

final class Plugin
{
    public function __construct()
    {
        $this->registerHooks();
    }

    private function registerHooks()
    {
        add_action('plugins_loaded', [$this, 'actionLoadTextDomain']);
    }

    public function actionLoadTextDomain()
    {
        load_plugin_textdomain(
            'dagpay-edd',
            false,
            ULTRALEET_DAGPAY_EDD_LANGUAGES_PATH
        );
    }
}
