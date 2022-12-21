<?php
/**
 * Plugin Name:     Mihdan: Clockwork
 * Plugin URI:      https://wordpress.org/plugins/mihdan-clockwork/
 * Description:     A plugin under WordPress for debugging using Clockwork
 * Author:          Mikhail Kobzarev
 * Author URI:      https://www.kobzarev.com/
 * Text Domain:     mihdan-clockwork
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Mihdan_Clockwork
 */
use Mihdan\Clockwork\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const MIHDAN_CLOCKWORK_PATH = __DIR__;

static $plugin;

if ( ! isset( $plugin ) ) {

	require_once MIHDAN_CLOCKWORK_PATH . '/vendor/autoload.php';

	function mihdan_clockwork() {
		return Core::get_instance();
	}

	$plugin = mihdan_clockwork();
}
