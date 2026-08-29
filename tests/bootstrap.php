<?php
/**
 * テストの下ごしらえ。
 *
 * 対象は料金の計算と設定値の検証で、いずれも WordPress のデータベースを
 * 触らない。そのため WordPress のテストスイートも MySQL も使わず、
 * 実際に呼ばれる数個の関数だけをここで用意する。
 * 起動が速く、CI で何も構築しなくてよい。
 *
 * @package Clinic_Fee_Simulator
 */

declare( strict_types = 1 );

define( 'ABSPATH', __DIR__ . '/' );
define( 'CLINIC_FEE_SIMULATOR_OPTION', 'cfs_settings' );

/** @var array<string, mixed> テスト側から差し替える options。 */
$GLOBALS['test_options'] = array();

/** @var array<int, array{setting: string, code: string, message: string, type: string}> 記録された検証エラー。 */
$GLOBALS['test_settings_errors'] = array();

/**
 * @param string $key     キー。
 * @param mixed  $default 既定値。
 * @return mixed
 */
function get_option( string $key, $default = false ) {
	return $GLOBALS['test_options'][ $key ] ?? $default;
}

/**
 * @param mixed $maybeint 値。
 */
function absint( $maybeint ): int {
	return abs( (int) $maybeint );
}

/**
 * @param mixed $key 値。
 */
function sanitize_key( $key ): string {
	return (string) preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
}

/**
 * @param string $str 値。
 */
function sanitize_textarea_field( string $str ): string {
	return trim( strip_tags( $str ) );
}

/**
 * @param string $text   文言。
 * @param string $domain テキストドメイン。
 */
function __( string $text, string $domain = 'default' ): string { // phpcs:ignore
	return $text;
}

/**
 * @param int|float $number 数値。
 */
function number_format_i18n( $number ): string {
	return number_format( (float) $number );
}

/**
 * @param string $setting 設定名。
 * @param string $code    コード。
 * @param string $message 文言。
 * @param string $type    種別。
 */
function add_settings_error( string $setting, string $code, string $message, string $type = 'error' ): void {
	$GLOBALS['test_settings_errors'][] = compact( 'setting', 'code', 'message', 'type' );
}

require_once __DIR__ . '/../plugins/clinic-fee-simulator/includes/class-clinic-fee-simulator-settings.php';
require_once __DIR__ . '/../plugins/clinic-fee-simulator/includes/class-clinic-fee-simulator-calculator.php';
