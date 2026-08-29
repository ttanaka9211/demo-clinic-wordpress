<?php
/**
 * アンインストール時の後始末
 *
 * 有効化しただけで残骸が残らないようにする。
 *
 * @package Clinic_Fee_Simulator
 */

declare( strict_types = 1 );

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'cfs_settings' );
