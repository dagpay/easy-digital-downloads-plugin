<?php
/*
Plugin Name: Dagpay for Easy Digital Downloads
Plugin URL: https://dagpay.io
Description: Dagpay gateway for Easy Digital Downloads.
Version: 1.0.0
Author: Dagpay
Author URI: https://dagpay.io
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: dagpay-edd
Contributors: Ultraleet, Dagcoin
*/

// define constants
define('ULTRALEET_DAGPAY_EDD_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('ULTRALEET_DAGPAY_EDD_SRC_PATH', ULTRALEET_DAGPAY_EDD_PATH . 'src' . DIRECTORY_SEPARATOR);
define('ULTRALEET_DAGPAY_EDD_LANGUAGES_PATH', basename(ULTRALEET_DAGPAY_EDD_PATH) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR);

// check PHP and WP version
require_once(ULTRALEET_DAGPAY_EDD_PATH . 'lib' . DIRECTORY_SEPARATOR . 'UltraleetWPRequirementsChecker.php');
$requirementsChecker = new UltraleetWPRequirementsChecker(array(
    'title' => 'Dagpay for Easy Digital Downloads',
    'php' => '7.1',
    'wp' => '4.9',
    'file' => __FILE__,
));
if ($requirementsChecker->passes()) {
    // setup autoload
    require_once(__DIR__ . '/vendor/autoload.php');

    // init plugin
    new \Ultraleet\DagpayEDD\Plugin();
}
