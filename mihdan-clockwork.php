<?php
/**
 * Plugin Name:     Mihdan: Clockwork
 * Plugin URI:      https://wordpress.org/plugins/mihdan-clockwork/
 * Description:     WordPress plugin for using clockwork
 * Author:          Mikhail Kobzarev
 * Author URI:      https://www.kobzarev.com/
 * Text Domain:     mihdan-clockwork
 * Domain Path:     /languages
 * Version:         0.1.1
 *
 * @package         Mihdan_Clockwork
 */
use Mihdan\Clockwork\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MIHDAN_CLOCKWORK_PATH', dirname( __FILE__ ) );

static $plugin;

if ( ! isset( $plugin ) ) {

	require_once MIHDAN_CLOCKWORK_PATH . '/vendor/autoload.php';

	function mihdan_clockwork() {
		return Core::get_instance();
	}

	$plugin = mihdan_clockwork();
}

// eof;
