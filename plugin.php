<?php
/**
 * Plugin Name: Dagpay for Easy Digital Downloads
 * Plugin URL: https://github.com/dagpay/easy-digital-downloads-plugin
 * Description: Dagpay gateway for Easy Digital Downloads.
 * Version: 1.0.1
 * Author: Dagpay
 * Author URI: https://dagpay.io
 * License: GNU General Public License v2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dagpay-edd
 * GitHub Plugin URI: https://github.com/dagpay/easy-digital-downloads-plugin
 * Requires WP: 4.6
 * Requires PHP: 7.1
 * Contributors: ultraleet, dagcoin975
 */

// define constants
define('ULTRALEET_DAGPAY_EDD_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('ULTRALEET_DAGPAY_EDD_SRC_PATH', ULTRALEET_DAGPAY_EDD_PATH . 'src' . DIRECTORY_SEPARATOR);
define('ULTRALEET_DAGPAY_EDD_LIB_PATH', ULTRALEET_DAGPAY_EDD_PATH . 'lib' . DIRECTORY_SEPARATOR);
define('ULTRALEET_DAGPAY_EDD_LANGUAGES_PATH', basename(ULTRALEET_DAGPAY_EDD_PATH) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR);
define('ULTRALEET_DAGPAY_EDD_PLUGIN_BASENAME', plugin_basename(__FILE__));

// check PHP and WP version
require_once(ULTRALEET_DAGPAY_EDD_LIB_PATH . 'UltraleetWPRequirementsChecker.php');
$requirementsChecker = new UltraleetWPRequirementsChecker(array(
    'title' => 'Dagpay for Easy Digital Downloads',
    'php' => '7.1',
    'wp' => '4.6',
    'file' => __FILE__,
));
if ($requirementsChecker->passes()) {
    // setup autoload
    require_once(__DIR__ . '/vendor/autoload.php');

    // load dagpay client
    require_once(ULTRALEET_DAGPAY_EDD_LIB_PATH . 'DagpayClient.php');

    // init plugin
    new \Ultraleet\DagpayEDD\Plugin();
}
