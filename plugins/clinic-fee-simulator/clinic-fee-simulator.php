<?php
/**
 * Plugin Name:       Clinic Fee Simulator
 * Plugin URI:        https://demo-clinic.hatchdogs.net/
 * Description:       整骨院向けの施術費用シミュレーター。ショートコード [clinic_simulator] で設置し、料金表は管理画面から編集できます。
 * Version:           1.0.0
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

define( 'CFS_VERSION', '1.0.0' );
define( 'CFS_FILE', __FILE__ );
define( 'CFS_DIR', plugin_dir_path( __FILE__ ) );
define( 'CFS_URL', plugin_dir_url( __FILE__ ) );
define( 'CFS_OPTION', 'cfs_settings' );

require_once CFS_DIR . 'includes/class-cfs-settings.php';
require_once CFS_DIR . 'includes/class-cfs-calculator.php';
require_once CFS_DIR . 'includes/class-cfs-shortcode.php';
require_once CFS_DIR . 'includes/class-cfs-rest.php';

/**
 * 起動。
 */
function cfs_bootstrap(): void {
	CFS_Settings::init();
	CFS_Shortcode::init();
	CFS_Rest::init();
}
add_action( 'plugins_loaded', 'cfs_bootstrap' );

/**
 * 有効化時に既定値を入れる。既存設定は壊さない。
 */
function cfs_activate(): void {
	if ( false === get_option( CFS_OPTION ) ) {
		add_option( CFS_OPTION, CFS_Settings::defaults() );
	}
}
register_activation_hook( CFS_FILE, 'cfs_activate' );
