<?php
/**
 * Plugin Name:       Clinic Contact
 * Plugin URI:        https://demo-clinic.hatchdogs.net/
 * Description:       問い合わせフォーム。ショートコード [clinic_contact] で設置します。送信は SMTP 経由（SPF/DKIM/DMARC を通すため PHP の mail() 直送は使いません）。
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            hatchdogs
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       clinic-contact
 *
 * @package Clinic_Contact
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

define( 'CLINIC_CONTACT_VERSION', '1.1.0' );
define( 'CLINIC_CONTACT_FILE', __FILE__ );
define( 'CLINIC_CONTACT_DIR', plugin_dir_path( __FILE__ ) );
define( 'CLINIC_CONTACT_URL', plugin_dir_url( __FILE__ ) );

require_once CLINIC_CONTACT_DIR . 'includes/class-clinic-contact-mailer.php';
require_once CLINIC_CONTACT_DIR . 'includes/class-clinic-contact-form.php';

/**
 * 起動。
 */
function clinic_contact_bootstrap(): void {
	load_plugin_textdomain( 'clinic-contact', false, dirname( plugin_basename( CLINIC_CONTACT_FILE ) ) . '/languages' );

	Clinic_Contact_Mailer::init();
	Clinic_Contact_Form::init();
}
add_action( 'plugins_loaded', 'clinic_contact_bootstrap' );
