<?php
/**
 * Plugin Name:       Clinic Fee Simulator
 * Plugin URI:        https://demo-clinic.hatchdogs.net/
 * Description:       整骨院向けの施術費用シミュレーター。ショートコード [clinic_simulator] で設置し、料金表は管理画面から編集できます。
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            hatchdogs
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       clinic-fee-simulator
 *
 * @package Clinic_Fee_Simulator
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

define( 'CLINIC_FEE_SIMULATOR_VERSION', '1.1.0' );
define( 'CLINIC_FEE_SIMULATOR_FILE', __FILE__ );
define( 'CLINIC_FEE_SIMULATOR_DIR', plugin_dir_path( __FILE__ ) );
define( 'CLINIC_FEE_SIMULATOR_URL', plugin_dir_url( __FILE__ ) );
define( 'CLINIC_FEE_SIMULATOR_OPTION', 'cfs_settings' );

require_once CLINIC_FEE_SIMULATOR_DIR . 'includes/class-clinic-fee-simulator-settings.php';
require_once CLINIC_FEE_SIMULATOR_DIR . 'includes/class-clinic-fee-simulator-calculator.php';
require_once CLINIC_FEE_SIMULATOR_DIR . 'includes/class-clinic-fee-simulator-shortcode.php';
require_once CLINIC_FEE_SIMULATOR_DIR . 'includes/class-clinic-fee-simulator-rest.php';

/**
 * 起動。
 */
function clinic_fee_simulator_bootstrap(): void {
	load_plugin_textdomain( 'clinic-fee-simulator', false, dirname( plugin_basename( CLINIC_FEE_SIMULATOR_FILE ) ) . '/languages' );

	Clinic_Fee_Simulator_Settings::init();
	Clinic_Fee_Simulator_Shortcode::init();
	Clinic_Fee_Simulator_Rest::init();
}
add_action( 'plugins_loaded', 'clinic_fee_simulator_bootstrap' );

/**
 * 有効化時に既定値を入れる。既存設定は壊さない。
 */
function clinic_fee_simulator_activate(): void {
	if ( false === get_option( CLINIC_FEE_SIMULATOR_OPTION ) ) {
		add_option( CLINIC_FEE_SIMULATOR_OPTION, Clinic_Fee_Simulator_Settings::defaults() );
	}
}
register_activation_hook( CLINIC_FEE_SIMULATOR_FILE, 'clinic_fee_simulator_activate' );
